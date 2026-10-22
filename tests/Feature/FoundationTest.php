<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Office;
use App\Models\OrganizationalUnit;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminResources;
use Database\Seeders\FoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FoundationSeeder::class);
    }

    private function user(string $role = 'superadmin'): User
    {
        $u = User::factory()->create();
        $u->roles()->attach(Role::where('code', $role)->first());

        return $u;
    }

    private function assetData(): array
    {
        return ['code' => 'AF-001', 'description' => 'Equipo de prueba', 'registered_on' => '2026-01-01', 'reason' => 'Alta inicial'];
    }

    public function test_authentication_and_permissions(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $u = $this->user('auditor');
        $this->actingAs($u)->get('/audit')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->post('/admin/assets', $this->assetData())->assertForbidden();
    }

    public function test_create_asset_audit_and_duplicate(): void
    {
        $this->actingAs($this->user())->post('/admin/assets', $this->assetData())->assertRedirect();
        $this->assertDatabaseHas('assets', ['code' => 'AF-001', 'current_person_id' => null]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'entity_type' => 'assets']);
        $this->post('/admin/assets', $this->assetData())->assertSessionHasErrors('code');
        $this->assertDatabaseCount('assets', 1);
    }

    public function test_all_admin_screens_render(): void
    {
        $this->actingAs($this->user());
        foreach (AdminResources::all() as $key => $config) {
            $this->get('/admin/'.$key)->assertOk();
            $this->get('/admin/'.$key.'/create')->assertOk();
        }
        $this->get('/roles')->assertOk();
        $this->get('/')->assertOk();
        $this->get('/dashboard')->assertOk();
        $this->get('/login')->assertRedirect();
    }

    public function test_institutional_relationships_and_cycles(): void
    {
        $u = OrganizationalUnit::create(['code' => 'U1', 'name' => 'Unidad 1']);
        $v = OrganizationalUnit::create(['code' => 'U2', 'name' => 'Unidad 2']);
        $o = Office::create(['code' => 'O1', 'name' => 'Oficina', 'organizational_unit_id' => $u->id]);
        $this->actingAs($this->user())->post('/admin/people', ['first_name' => 'Ana', 'last_name' => 'Pérez', 'organizational_unit_id' => $v->id, 'office_id' => $o->id, 'active' => 1, 'reason' => 'Registro'])->assertSessionHasErrors('office_id');
        $this->put('/admin/units/'.$u->id, ['code' => 'U1', 'name' => 'Unidad', 'parent_id' => $u->id, 'active' => 1, 'reason' => 'Cambio'])->assertSessionHasErrors('parent_id');
    }

    public function test_stale_asset_update_is_rejected(): void
    {
        $this->actingAs($this->user())->post('/admin/assets', $this->assetData());
        $a = Asset::first();
        $token = hash('sha256', json_encode($a->getAttributes()));
        $data = [...$this->assetData(), 'description' => 'Actualizado', 'record_token' => $token, 'version' => 1];
        $this->put('/admin/assets/'.$a->id, $data)->assertRedirect();
        $this->put('/admin/assets/'.$a->id, $data)->assertStatus(409);
        $this->assertDatabaseHas('assets', ['version' => 2]);
    }

    public function test_audit_failure_rolls_back_asset(): void
    {
        AuditLog::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        try {
            $this->withoutExceptionHandling()->actingAs($this->user())->post('/admin/assets', $this->assetData());
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Audit unavailable', $e->getMessage());
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_login_logout_inactive_and_password_redaction(): void
    {
        $u = $this->user();
        $this->post('/login', ['email' => $u->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertRedirect('/panel');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->actingAs($u)->post('/admin/users', ['name' => 'Usuario', 'email' => 'new@example.test', 'password' => 'StrongPassword123', 'active' => 1, 'roles' => [Role::where('code', 'auditor')->value('id')], 'reason' => 'Crear usuario'])->assertRedirect();
        $log = AuditLog::where('action', 'create')->where('entity_type', 'users')->first();
        $this->assertArrayNotHasKey('password', $log->new_values);
        $u->active = false;
        $u->save();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_audit_is_immutable_through_model(): void
    {
        $log = AuditLog::create(['action' => 'test', 'entity_type' => 'test']);
        $this->expectException(\LogicException::class);
        $log->update(['action' => 'changed']);
    }

    public function test_seed_is_idempotent(): void
    {
        $this->seed(FoundationSeeder::class);
        $this->assertDatabaseCount('roles', 6);
        $this->assertDatabaseCount('physical_statuses', 4);
    }
}
