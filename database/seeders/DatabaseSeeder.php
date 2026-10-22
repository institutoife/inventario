<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FoundationSeeder::class);
        if (config('initial_admin.email')) {
            $this->call(InitialAdminSeeder::class);
        }
    }
}
