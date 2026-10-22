<?php

namespace Tests\Feature\Filament;

use App\Actions\Assets\SaveAsset;
use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\EditAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\AuditLogsRelationManager;
use App\Models\AdministrativeStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Office;
use App\Models\OrganizationalUnit;
use App\Models\Person;
use App\Models\PhysicalStatus;
use App\Queries\AssetVisibility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;

class AssetsTest extends FilamentTestCase
{
    public function test_list_create_view_and_edit_pages_render(): void
    {
        $asset = $this->makeAsset();
        foreach (['index' => [], 'create' => [], 'view' => ['record' => $asset], 'edit' => ['record' => $asset]] as $page => $parameters) {
            $this->get(AssetResource::getUrl($page, $parameters))->assertOk();
        }
        Livewire::test(ListAssets::class)->assertCanSeeTableRecords([$asset]);
        Livewire::test(ViewAsset::class, ['record' => $asset->id])->assertSee('Datos principales')->assertSee('Ubicación actual');
    }

    public static function searchFields(): array
    {
        return [['code'], ['description'], ['brand'], ['model'], ['serial_number']];
    }

    #[DataProvider('searchFields')]
    public function test_search_finds_each_identifying_field(string $field): void
    {
        $target = $this->makeAsset([$field => 'BUSQUEDA-UNICA']);
        $other = $this->makeAsset(['code' => 'AF-002', 'description' => 'Mesa']);
        Livewire::test(ListAssets::class)->searchTable('BUSQUEDA-UNICA')
            ->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other]);
    }

    public function test_filters_combine_and_can_be_reset(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Administración']);
        $office = Office::create(['code' => 'O1', 'name' => 'Contabilidad', 'organizational_unit_id' => $unit->id]);
        $otherUnit = OrganizationalUnit::create(['code' => 'U2', 'name' => 'Operaciones']);
        $status = PhysicalStatus::where('code', 'B')->sole();
        $target = $this->makeAsset(['organizational_unit_id' => $unit->id, 'office_id' => $office->id, 'physical_status_id' => $status->id]);
        $sameUnit = $this->makeAsset(['code' => 'AF-002', 'organizational_unit_id' => $unit->id, 'physical_status_id' => PhysicalStatus::where('code', 'M')->sole()->id]);
        $other = $this->makeAsset(['code' => 'AF-003', 'organizational_unit_id' => $otherUnit->id, 'physical_status_id' => $status->id]);
        Livewire::test(ListAssets::class)->filterTable('organizational_unit_id', $unit->id)
            ->filterTable('physical_status_id', $status->id)->filterTable('office_id', $office->id)
            ->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$sameUnit, $other])
            ->resetTableFilters()->assertCanSeeTableRecords([$target, $sameUnit, $other]);
    }

    public function test_category_situation_responsible_and_date_filters(): void
    {
        $category = AssetCategory::create(['code' => 'EQ', 'name' => 'Equipos']);
        $person = Person::create(['first_name' => 'Ana', 'last_name' => 'Pérez']);
        $target = $this->makeAsset(['asset_category_id' => $category->id, 'current_person_id' => $person->id]);
        $other = $this->makeAsset(['code' => 'AF-002', 'registered_on' => today()->subYear()->toDateString()]);
        Livewire::test(ListAssets::class)->filterTable('asset_category_id', $category->id)
            ->filterTable('current_person_id', $person->id)->filterTable('administrative_status_id', $target->administrative_status_id)
            ->filterTable('registered_on', ['from' => today()->subWeek()->toDateString(), 'until' => today()->toDateString()])
            ->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other]);
    }

    public function test_pagination_and_sorting(): void
    {
        $first = $this->makeAsset(['code' => 'AA-001']);
        for ($index = 2; $index <= 26; $index++) {
            $this->makeAsset(['code' => sprintf('AF-%03d', $index)]);
        }
        $last = $this->makeAsset(['code' => 'ZZ-027']);
        Livewire::test(ListAssets::class)->sortTable('code', 'asc')->assertCanSeeTableRecords([$first])
            ->assertCanNotSeeTableRecords([$last])->call('gotoPage', 2)->assertCanSeeTableRecords([$last]);
    }

    public function test_creation_uses_domain_and_records_one_audit_entry(): void
    {
        Livewire::test(CreateAsset::class)->fillForm($this->assetData(['value' => '123.45']))->call('create')->assertHasNoFormErrors();
        $asset = Asset::sole();
        $this->assertSame('123.45', $asset->value);
        $this->assertNull($asset->current_person_id);
        $this->assertSame('available', $asset->administrativeStatus->code);
        $log = AuditLog::where('entity_type', 'assets')->sole();
        $this->assertSame($this->operator->id, $log->user_id);
        $this->assertSame('Registro de inventario verificado', $log->reason);
        $this->assertSame($asset->code, $log->new_values['code']);
    }

    public function test_duplicate_code_is_rejected(): void
    {
        $this->makeAsset();
        Livewire::test(CreateAsset::class)->fillForm($this->assetData())->call('create')->assertHasFormErrors(['code']);
        $this->assertDatabaseCount('assets', 1);
    }

    public static function invalidData(): array
    {
        return [
            'description' => ['description', '', 'description'], 'reason' => ['reason', '', 'reason'],
            'whitespace reason' => ['reason', '   ', 'reason'], 'negative value' => ['value', '-1.00', 'value'],
            'decimal precision' => ['value', '1.234', 'value'], 'invalid reference' => ['budget_item_id', 999999, 'budget_item_id'],
        ];
    }

    #[DataProvider('invalidData')]
    public function test_invalid_creation_is_rejected(string $field, mixed $value, string $error): void
    {
        Livewire::test(CreateAsset::class)->fillForm($this->assetData([$field => $value]))->call('create')->assertHasFormErrors([$error]);
        $this->assertDatabaseCount('assets', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_future_registration_date_is_rejected(): void
    {
        Livewire::test(CreateAsset::class)->fillForm($this->assetData(['registered_on' => today()->addDay()->toDateString()]))
            ->call('create')->assertHasFormErrors(['registered_on']);
    }

    public function test_invalid_office_and_deposit_are_rejected_by_shared_domain(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Unidad']);
        $otherUnit = OrganizationalUnit::create(['code' => 'U2', 'name' => 'Otra unidad']);
        $office = Office::create(['code' => 'O1', 'name' => 'Oficina', 'organizational_unit_id' => $unit->id]);
        $deposit = Location::create(['code' => 'D1', 'name' => 'Depósito', 'office_id' => $office->id, 'is_deposit' => true]);
        $this->post('/admin/assets', $this->assetData(['organizational_unit_id' => $otherUnit->id, 'office_id' => $office->id]))->assertSessionHasErrors('office_id');
        Livewire::test(CreateAsset::class)->fillForm($this->assetData(['organizational_unit_id' => $unit->id, 'office_id' => $office->id, 'location_id' => $deposit->id]))
            ->call('create')->assertHasFormErrors(['location_id']);
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_inactive_catalog_is_rejected(): void
    {
        $category = AssetCategory::create(['code' => 'OLD', 'name' => 'Desactivada', 'active' => false]);
        $this->expectException(ValidationException::class);
        app(SaveAsset::class)->execute($this->operator, $this->assetData(['asset_category_id' => $category->id]));
    }

    public function test_edit_preserves_audit_and_increments_version(): void
    {
        $asset = $this->makeAsset();
        Livewire::test(EditAsset::class, ['record' => $asset->id])->fillForm(['description' => 'Descripción corregida', 'reason' => 'Corrección documentada'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame(2, $asset->refresh()->version);
        $log = AuditLog::where('entity_type', 'assets')->sole();
        $this->assertSame('Computadora institucional', $log->old_values['description']);
        $this->assertSame('Descripción corregida', $log->new_values['description']);
    }

    public function test_two_editors_cannot_overwrite_each_other(): void
    {
        $asset = $this->makeAsset();
        $first = Livewire::test(EditAsset::class, ['record' => $asset->id]);
        $second = Livewire::test(EditAsset::class, ['record' => $asset->id]);
        $first->fillForm(['description' => 'Primera edición', 'reason' => 'Verificado'])->call('save')->assertHasNoFormErrors();
        $second->fillForm(['description' => 'Edición obsoleta', 'reason' => 'Otro cambio'])->call('save')->assertHasFormErrors(['code']);
        $this->assertSame('Primera edición', $asset->refresh()->description);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_version_cannot_be_modified_by_livewire_client(): void
    {
        $asset = $this->makeAsset();
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(EditAsset::class, ['record' => $asset->id])->set('recordVersion', 200);
    }

    public function test_audit_failure_rolls_back_filament_creation(): void
    {
        AuditLog::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        try {
            Livewire::test(CreateAsset::class)->fillForm($this->assetData())->call('create');
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_read_only_user_has_no_create_or_edit_access_including_direct_urls(): void
    {
        $asset = $this->makeAsset();
        $this->actingAs($this->makeUser('auditor'));
        $this->get(AssetResource::getUrl())->assertOk();
        $this->get(AssetResource::getUrl('view', ['record' => $asset]))->assertOk();
        $this->get(AssetResource::getUrl('create'))->assertForbidden();
        $this->get(AssetResource::getUrl('edit', ['record' => $asset]))->assertForbidden();
        Livewire::test(ListAssets::class)->assertTableActionHidden('edit', $asset);
        Livewire::test(CreateAsset::class)->assertForbidden();
        Livewire::test(EditAsset::class, ['record' => $asset->id])->assertForbidden();
    }

    public function test_revoking_permission_blocks_already_mounted_edit(): void
    {
        $asset = $this->makeAsset();
        $editor = Livewire::test(EditAsset::class, ['record' => $asset->id])->fillForm(['description' => 'No autorizado', 'reason' => 'Prueba']);
        $this->operator->roles()->detach();
        $editor->call('save')->assertNotFound(); // The visibility query no longer resolves this record.
        $this->assertSame('Computadora institucional', $asset->refresh()->description);
    }

    public function test_policy_and_query_deny_unprivileged_and_inactive_users(): void
    {
        $asset = $this->makeAsset();
        $unprivileged = $this->makeUser(null);
        $this->assertSame(0, AssetVisibility::forUser($unprivileged)->count());
        $this->assertFalse(Gate::forUser($unprivileged)->allows('create', Asset::class));
        $this->operator->update(['active' => false]);
        $this->assertFalse(Gate::forUser($this->operator)->allows('view', $asset));
        $this->assertFalse(Gate::forUser($this->operator)->allows('update', $asset));
        $this->assertSame(0, AssetVisibility::forUser($this->operator)->count());
    }

    public function test_even_superadmin_cannot_delete_or_perform_unimplemented_movements(): void
    {
        $asset = $this->makeAsset();
        foreach (['delete', 'restore', 'forceDelete', 'assign', 'transfer', 'relieve', 'sendToWarehouse', 'reportMissing', 'replace', 'revalue'] as $ability) {
            $this->assertFalse(Gate::allows($ability, $asset));
        }
        Livewire::test(ListAssets::class)->assertTableActionDoesNotExist('delete');
    }

    public static function protectedFields(): array
    {
        return [['current_person_id', 1], ['administrative_status_id', 1], ['active', false]];
    }

    #[DataProvider('protectedFields')]
    public function test_domain_rejects_protected_fields(string $field, mixed $value): void
    {
        $this->expectException(ValidationException::class);
        app(SaveAsset::class)->execute($this->operator, $this->assetData([$field => $value]));
    }

    public function test_an_assigned_asset_cannot_be_relocated_through_edit(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Destino']);
        $asset = $this->makeAsset();
        $asset->update(['administrative_status_id' => AdministrativeStatus::where('code', 'assigned')->sole()->id]);
        Livewire::test(EditAsset::class, ['record' => $asset->id])->fillForm(['organizational_unit_id' => $unit->id, 'reason' => 'Intento de traslado'])
            ->call('save')->assertHasFormErrors(['organizational_unit_id']);
        $this->assertNull($asset->refresh()->organizational_unit_id);
    }

    public function test_asset_history_only_contains_its_own_audit_entries(): void
    {
        $asset = app(SaveAsset::class)->execute($this->operator, $this->assetData());
        $other = app(SaveAsset::class)->execute($this->operator, $this->assetData(['code' => 'AF-002']));
        $history = Livewire::test(AuditLogsRelationManager::class, ['ownerRecord' => $asset, 'pageClass' => ViewAsset::class]);
        $history->assertCanSeeTableRecords($asset->auditLogs)->assertCanNotSeeTableRecords($other->auditLogs);
        $this->actingAs($this->makeUser('asset_manager'));
        $this->assertFalse(AuditLogsRelationManager::canViewForRecord($asset, ViewAsset::class));
    }
}
