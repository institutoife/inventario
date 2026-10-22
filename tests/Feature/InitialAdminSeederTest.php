<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InitialAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_once_with_hashed_password_and_audit(): void
    {
        config(['initial_admin' => ['name' => 'Administrador', 'email' => 'admin@example.test', 'password' => 'InitialPassword123!']]);
        $this->seed(DatabaseSeeder::class);
        $user = User::sole();
        $this->assertTrue(Hash::check('InitialPassword123!', $user->password));
        $this->assertTrue($user->hasPermission('users.manage'));
        $this->assertArrayNotHasKey('password', AuditLog::sole()->new_values);

        config(['initial_admin.password' => 'DifferentPassword123!']);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertTrue(Hash::check('InitialPassword123!', $user->fresh()->password));
    }

    public function test_rejects_weak_password_without_creating_user(): void
    {
        config(['initial_admin' => ['name' => 'Administrador', 'email' => 'admin@example.test', 'password' => 'short']]);
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }
        $this->assertDatabaseCount('users', 0);
    }
}
