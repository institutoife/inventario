<?php

namespace App\Services;

use App\Models;

class AdminResources
{
    public static function all(): array
    {
        $catalog = ['code' => 'Código', 'name' => 'Nombre', 'active' => 'Activo'];

        return [
            'units' => [Models\OrganizationalUnit::class, 'Unidades institucionales', 'structure.manage', [...$catalog, 'parent_id' => 'Unidad superior']],
            'offices' => [Models\Office::class, 'Oficinas', 'structure.manage', [...$catalog, 'organizational_unit_id' => 'Unidad']],
            'locations' => [Models\Location::class, 'Ubicaciones', 'structure.manage', [...$catalog, 'office_id' => 'Oficina', 'is_deposit' => 'Es depósito']],
            'people' => [Models\Person::class, 'Responsables', 'people.manage', ['first_name' => 'Nombres', 'last_name' => 'Apellidos', 'institutional_id' => 'Identificación institucional', 'position_id' => 'Cargo', 'rank_id' => 'Grado/rango', 'organizational_unit_id' => 'Unidad', 'office_id' => 'Oficina', 'active' => 'Activo']],
            'budget-items' => [Models\BudgetItem::class, 'Partidas', 'catalogs.manage', $catalog],
            'categories' => [Models\AssetCategory::class, 'Categorías', 'catalogs.manage', [...$catalog, 'parent_id' => 'Categoría superior']],
            'physical-statuses' => [Models\PhysicalStatus::class, 'Estados físicos', 'catalogs.manage', $catalog],
            'administrative-statuses' => [Models\AdministrativeStatus::class, 'Situaciones administrativas', 'catalogs.manage', $catalog],
            'positions' => [Models\Position::class, 'Cargos', 'catalogs.manage', $catalog],
            'ranks' => [Models\Rank::class, 'Grados / rangos', 'catalogs.manage', $catalog],
            'assets' => [Models\Asset::class, 'Inventario base', 'assets.view', ['code' => 'Código', 'description' => 'Descripción', 'registered_on' => 'Fecha de alta', 'budget_item_id' => 'Partida', 'asset_category_id' => 'Categoría', 'physical_status_id' => 'Estado físico', 'organizational_unit_id' => 'Unidad', 'office_id' => 'Oficina', 'location_id' => 'Ubicación', 'origin' => 'Procedencia', 'incorporation_method' => 'Forma de incorporación', 'value' => 'Valor', 'brand' => 'Marca', 'model' => 'Modelo', 'serial_number' => 'Serie', 'observations' => 'Observaciones']],
            'users' => [Models\User::class, 'Usuarios', 'users.manage', ['name' => 'Nombre', 'email' => 'Correo', 'password' => 'Contraseña', 'active' => 'Activo', 'roles' => 'Roles']],
        ];
    }

    public static function get(string $key): array
    {
        return self::all()[$key] ?? abort(404);
    }

    public static function reference(string $key, string $field): ?string
    {
        return match ($field) {
            'parent_id' => self::get($key)[0],
            'organizational_unit_id' => Models\OrganizationalUnit::class,'office_id' => Models\Office::class,'location_id' => Models\Location::class,
            'position_id' => Models\Position::class,'rank_id' => Models\Rank::class,'budget_item_id' => Models\BudgetItem::class,
            'asset_category_id' => Models\AssetCategory::class,'physical_status_id' => Models\PhysicalStatus::class,
            default => null,
        };
    }
}
