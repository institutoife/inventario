<?php

namespace Tests\Feature;

use App\Models\AssetCategory;
use App\Models\BudgetItem;
use App\Models\Position;
use App\Models\Rank;
use Database\Seeders\InitialCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_catalogs_are_populated_and_repeated_without_overwriting(): void
    {
        $this->seed(InitialCatalogSeeder::class);
        $this->assertDatabaseCount('asset_categories', 9);
        $this->assertDatabaseCount('budget_items', 9);
        $this->assertDatabaseCount('ranks', 31);
        $this->assertDatabaseCount('positions', 6);
        $this->assertDatabaseCount('asset_origins', 7);
        $this->assertDatabaseCount('incorporation_methods', 8);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('organizational_units', 0);
        $this->assertDatabaseHas('ranks', ['code' => 'CAP', 'name' => 'Capitán']);
        $this->assertDatabaseHas('ranks', ['code' => 'CAP-SERV', 'name' => 'Capitán de Servicios']);
        foreach ([AssetCategory::class, BudgetItem::class, Rank::class, Position::class] as $model) {
            $model::first()->update(['name' => 'Nombre institucional editado', 'active' => false]);
        }
        $this->seed(InitialCatalogSeeder::class);
        foreach ([AssetCategory::class => 9, BudgetItem::class => 9, Rank::class => 31, Position::class => 6] as $model => $count) {
            $this->assertSame($count, $model::count());
            $this->assertSame('Nombre institucional editado', $model::first()->name);
            $this->assertFalse($model::first()->active);
        }
    }
}
