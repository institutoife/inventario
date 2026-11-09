<?php

namespace Tests\Feature;

use App\Actions\Assets\SaveAsset;
use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Models\AssetOrigin;
use App\Models\IncorporationMethod;
use App\Models\Location;
use App\Models\Office;
use App\Models\OrganizationalUnit;
use Database\Seeders\AssetCatalogSeeder;
use Database\Seeders\DemoOrganizationSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Filament\FilamentTestCase;

class CatalogSeedersTest extends FilamentTestCase
{
    public function test_exact_catalog_values_and_repeated_seeding_preserve_edits(): void
    {
        $this->seed(AssetCatalogSeeder::class);
        $this->assertSame(['VICE-MINISTERIO', 'COMANDO GENERAL', 'COMANDO DEPARTAMENTAL', 'GOBIERNO MUNICIPAL', 'GOBERNACION', 'OTBS', 'OTROS'], AssetOrigin::orderBy('id')->pluck('name')->all());
        $this->assertSame(['Compra', 'Donación', 'Transferencia', 'Asignación', 'Comodato', 'Depósito judicial', 'Incautación', 'Transferencia definitiva'], IncorporationMethod::orderBy('id')->pluck('name')->all());
        AssetOrigin::where('code', 'OTROS')->update(['name' => 'Otro origen institucional', 'active' => false]);
        $this->seed(AssetCatalogSeeder::class);
        $this->assertDatabaseCount('asset_origins', 7);
        $this->assertDatabaseCount('incorporation_methods', 8);
        $this->assertDatabaseHas('asset_origins', ['code' => 'OTROS', 'name' => 'Otro origen institucional', 'active' => false]);
    }

    public function test_demo_structure_is_coherent_and_not_overwritten(): void
    {
        $this->seed(DemoOrganizationSeeder::class);
        $this->assertDatabaseCount('organizational_units', 4);
        $this->assertDatabaseCount('offices', 5);
        $this->assertDatabaseCount('locations', 6);
        $this->assertDatabaseCount('positions', 3);
        $root = OrganizationalUnit::where('code', 'DEMO-INSTITUCION')->sole();
        $this->assertSame(3, OrganizationalUnit::where('parent_id', $root->id)->count());
        foreach (Office::all() as $office) {
            $this->assertNotNull(OrganizationalUnit::find($office->organizational_unit_id));
        }
        foreach (Location::all() as $location) {
            $this->assertNotNull(Office::find($location->office_id));
        }
        $root->update(['name' => 'Nombre editado']);
        $this->seed(DemoOrganizationSeeder::class);
        $this->assertDatabaseCount('organizational_units', 4);
        $this->assertDatabaseCount('offices', 5);
        $this->assertDatabaseHas('organizational_units', ['id' => $root->id, 'name' => 'Nombre editado']);
    }

    public function test_filament_asset_uses_catalog_values_and_rejects_unknown_values(): void
    {
        $this->seed(AssetCatalogSeeder::class);
        Livewire::test(CreateAsset::class)->fillForm($this->assetData(['origin' => 'GOBIERNO MUNICIPAL', 'incorporation_method' => 'Compra']))->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('assets', ['origin' => 'GOBIERNO MUNICIPAL', 'incorporation_method' => 'Compra']);
        try {
            app(SaveAsset::class)->execute($this->operator, $this->assetData(['code' => 'INVALIDO', 'origin' => 'No existe']));
            $this->fail('Expected invalid catalogue rejection');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('origin', $e->errors());
        }
        IncorporationMethod::where('code', 'COMPRA')->update(['active' => false]);
        try {
            app(SaveAsset::class)->execute($this->operator, $this->assetData(['code' => 'INACTIVO', 'incorporation_method' => 'Compra']));
            $this->fail('Expected inactive catalogue rejection');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('incorporation_method', $e->errors());
        }
    }

    public function test_unchanged_historical_text_remains_editable(): void
    {
        $asset = $this->makeAsset(['origin' => 'Procedencia histórica', 'incorporation_method' => 'Registro antiguo']);
        $saved = app(SaveAsset::class)->execute($this->operator, $this->assetData(['origin' => $asset->origin, 'incorporation_method' => $asset->incorporation_method, 'version' => 1]), $asset);
        $this->assertSame('Procedencia histórica', $saved->origin);
        $this->assertSame('Registro antiguo',$saved->incorporation_method);
    }
}
