<?php

namespace Tests\Feature;

use App\Actions\SaveFoundation;
use App\Filament\Resources\Offices\Pages\CreateRecord as CreateOffice;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Units\Pages\EditRecord as EditUnit;
use App\Filament\Resources\Users\Pages\CreateRecord as CreateUser;
use App\Models\AuditLog;
use App\Models\OrganizationalUnit;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminResources;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\Filament\FilamentTestCase;

class FoundationTest extends FilamentTestCase
{
    public function test_all_management_screens_are_inside_filament(): void
    {
        foreach (AdminResources::all() as $key => $config) {
            if ($key === 'assets') {
                continue;
            }
            $this->get('/panel/'.$key)->assertOk();
            $this->get('/panel/'.$key.'/create')->assertOk();
        }
        $this->get('/panel/roles')->assertOk();
        foreach (['/admin/users', '/admin/assets', '/roles', '/audit', '/dashboard', '/login'] as $path) {
            $this->get($path)->assertNotFound();
        }
        $this->post('/login', [])->assertNotFound();
    }

    public function test_user_creation_inside_filament_is_hashed_and_audited(): void
    {
        Livewire::test(CreateUser::class)->fillForm(['name' => 'Nuevo usuario', 'email' => 'new@example.test', 'password' => 'StrongPassword123!', 'active' => true, 'roles' => [Role::where('code', 'auditor')->sole()->id], 'reason' => 'Registro autorizado'])->call('create')->assertHasNoFormErrors();
        $user = User::where('email', 'new@example.test')->sole();
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        $this->assertSame('auditor', $user->roles->sole()->code);
        $this->assertArrayNotHasKey('password', AuditLog::where('entity_type', 'users')->where('entity_id', $user->id)->sole()->new_values);
    }

    public function test_office_can_be_created_with_its_unit(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Unidad']);
        Livewire::test(CreateOffice::class)->fillForm(['code' => 'O1', 'name' => 'Oficina', 'organizational_unit_id' => $unit->id, 'active' => true, 'reason' => 'Nueva oficina'])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('offices', ['code' => 'O1', 'organizational_unit_id' => $unit->id]);
    }

    public function test_cycles_are_rejected_in_filament(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Unidad']);
        Livewire::test(EditUnit::class, ['record' => $unit->id])->fillForm(['parent_id' => $unit->id, 'reason' => 'Cambio'])->call('save')->assertHasFormErrors(['parent_id']);
    }

    public function test_unauthorized_users_cannot_open_or_save_management(): void
    {
        $this->actingAs($this->makeUser('auditor'));
        $this->get('/panel/users')->assertForbidden();
        Livewire::test(CreateUser::class)->assertForbidden();
        $this->expectException(AuthorizationException::class);
        app(SaveFoundation::class)->execute('units', ['code' => 'X', 'name' => 'X', 'active' => true, 'reason' => 'Intento']);
    }

    public function test_stale_master_update_and_audit_failure_preserve_data(): void
    {
        $unit = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Unidad']);
        $unit->refresh();
        $token = hash('sha256', json_encode($unit->getAttributes()));
        $data = ['code' => 'U1', 'name' => 'Actualizada', 'active' => true, 'reason' => 'Corrección'];
        app(SaveFoundation::class)->execute('units', $data, $unit, $token);
        try {
            app(SaveFoundation::class)->execute('units', $data, $unit, $token);
            $this->fail('Expected stale rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        AuditLog::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        try {
            app(SaveFoundation::class)->execute('units', ['code' => 'U2', 'name' => 'Otra', 'active' => true, 'reason' => 'Alta']);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Audit unavailable', $e->getMessage());
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertDatabaseMissing('organizational_units', ['code' => 'U2']);
    }

    public function test_role_permissions_are_saved_and_audited_in_panel(): void
    {
        $role = Role::where('code', 'auditor')->sole();
        Livewire::test(EditRole::class, ['record' => $role->id])->fillForm(['permissions' => [], 'reason' => 'Retiro de permisos'])->call('save')->assertHasNoFormErrors();
        $this->assertCount(0, $role->fresh()->permissions);
        $this->assertDatabaseHas('audit_logs', ['entity_type' => 'roles', 'entity_id' => $role->id, 'action' => 'permissions.update']);
        Livewire::test(EditRole::class, ['record' => Role::where('code', 'superadmin')->sole()->id])->assertForbidden();
    }
}
