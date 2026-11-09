<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([AssetOriginSeeder::class, IncorporationMethodSeeder::class, AssetCategorySeeder::class, BudgetItemSeeder::class]);
        });
    }
}
