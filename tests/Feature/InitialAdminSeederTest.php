<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\InitialAdminSeeder;
use Tests\Feature\Filament\FilamentTestCase;

class InitialAdminSeederTest extends FilamentTestCase
{
    public function test_old_seeder_delegates_to_user_seeder(): void
    {
        $this->seed(InitialAdminSeeder::class);
        $this->seed(InitialAdminSeeder::class);
        $this->assertSame(1, User::whereKeyNot($this->operator->id)->count());
    }
}
