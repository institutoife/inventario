<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Filament\FilamentTestCase;

class UserSeederTest extends FilamentTestCase
{
    public function test_seeder_uses_editable_credentials_in_its_own_file_and_is_idempotent(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::whereKeyNot($this->operator->id)->sole();
        $this->assertNotSame('unknown', password_get_info($user->password)['algoName']);
        $this->assertSame('superadmin', $user->roles->sole()->code);
        $this->assertArrayNotHasKey('password', AuditLog::where('entity_type', 'users')->where('entity_id', $user->id)->sole()->new_values);
        $user->update(['password' => 'DifferentPassword123!', 'active' => false]);
        $user->roles()->sync([Role::where('code', 'auditor')->sole()->id]);
        $this->seed(UserSeeder::class);
        $this->assertTrue(Hash::check('DifferentPassword123!', $user->fresh()->password));
        $this->assertFalse($user->fresh()->active);
        $this->assertSame('auditor', $user->fresh()->roles->sole()->code);
    }

    public function test_existing_user_is_not_promoted(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::whereKeyNot($this->operator->id)->sole();
        $user->roles()->detach();
        $user->update(['password' => 'ExistingPassword123!']);
        $this->seed(UserSeeder::class);
        $this->assertCount(0, $user->fresh()->roles);
        $this->assertTrue(Hash::check('ExistingPassword123!', $user->fresh()->password));
    }
}
