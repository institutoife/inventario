<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([AssetCatalogSeeder::class, RankSeeder::class, PositionSeeder::class]);
        });
    }
}
