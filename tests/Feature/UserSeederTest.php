<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\FoundationSeeder;
use Database\Seeders\InitialAdminSeeder;
use Database\Seeders\UserSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FoundationSeeder::class);
        config(['initial_admin' => ['name' => 'Administrador', 'email' => 'admin@example.test', 'password' => 'SeederPassword123!']]);
    }

    public function test_created_admin_has_hashed_password_role_audit_and_panel_access(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::sole();
        $this->assertTrue(Hash::check('SeederPassword123!', $user->password));
        $this->assertTrue($user->active);
        $this->assertSame('superadmin', $user->roles->sole()->code);
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertArrayNotHasKey('password', AuditLog::sole()->new_values);
        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'SeederPassword123!'])->assertRedirect('/panel');
        $this->assertAuthenticatedAs($user);
    }

    public function test_repeating_seeder_does_not_reset_password_roles_or_active_state(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::sole();
        $originalHash = $user->password;
        $user->roles()->sync([Role::where('code', 'auditor')->sole()->id]);
        $user->update(['active' => false]);
        config(['initial_admin.password' => 'DifferentPassword123!']);
        $this->seed(UserSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertSame($originalHash, $user->refresh()->password);
        $this->assertFalse($user->active);
        $this->assertSame('auditor', $user->roles->sole()->code);
    }

    public function test_existing_account_with_same_email_is_not_promoted(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);
        $this->seed(UserSeeder::class);
        $this->assertCount(0, $user->roles);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_local_environment_creates_admin_with_generated_password_when_not_configured(): void
    {
        $this->app['env'] = 'local';
        config(['initial_admin.email' => null, 'initial_admin.password' => null]);
        $this->seed(UserSeeder::class);
        $user = User::sole();
        $this->assertSame('admin@inventario.local', $user->email);
        $this->assertNotSame('unknown', password_get_info($user->password)['algoName']);
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertArrayNotHasKey('password', AuditLog::sole()->new_values);
    }

    public function test_production_does_not_create_an_implicit_admin(): void
    {
        $this->app['env'] = 'production';
        config(['initial_admin.email' => null, 'initial_admin.password' => null]);
        $this->artisan('db:seed', ['--class' => UserSeeder::class, '--force' => true])->assertExitCode(0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_weak_configured_password_is_rejected(): void
    {
        config(['initial_admin.password' => 'short']);
        try {
            $this->seed(UserSeeder::class);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_previous_seeder_remains_a_compatible_entry_point(): void
    {
        $this->seed(InitialAdminSeeder::class);
        $this->assertSame('admin@example.test', User::sole()->email);
        $this->assertSame('superadmin', User::sole()->roles->sole()->code);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_failed_audit_rolls_back_user_and_role_assignment(): void
    {
        AuditLog::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        try {
            $this->seed(UserSeeder::class);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('role_user', 0);
    }
}
