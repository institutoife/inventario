<?php

namespace Database\Seeders;

use App\Models\AdministrativeStatus;
use App\Models\Permission;
use App\Models\PhysicalStatus;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['assets.view', 'assets.create', 'assets.update', 'assets.assign', 'assets.transfer', 'assets.handover', 'assets.deposit', 'assets.missing', 'assets.replace', 'assets.revalue', 'vehicles.manage', 'documents.generate', 'operations.cancel', 'users.manage', 'structure.manage', 'people.manage', 'catalogs.manage', 'audit.view'];
        foreach ($permissions as $code) {
            Permission::firstOrCreate(['code' => $code], ['name' => $code]);
        }
        $roles = ['superadmin' => 'Superadministrador', 'asset_admin' => 'Administrador de activos fijos', 'asset_manager' => 'Encargado de activos fijos', 'deposit_manager' => 'Encargado de depósito', 'unit_manager' => 'Responsable de unidad', 'auditor' => 'Auditor / consulta'];
        foreach ($roles as $code => $name) {
            $role = Role::firstOrCreate(['code' => $code], ['name' => $name]);
            $grants = match ($code) {
                'superadmin','asset_admin' => $permissions,
                'asset_manager' => ['assets.view', 'assets.create', 'assets.update', 'people.manage', 'catalogs.manage'],
                'deposit_manager','unit_manager' => ['assets.view'],
                'auditor' => ['assets.view', 'audit.view'],
            };
            $role->permissions()->syncWithoutDetaching(Permission::whereIn('code', $grants)->pluck('id'));
        }
        foreach (['B' => 'Bueno', 'R' => 'Regular', 'M' => 'Malo', 'MD' => 'Malo en desuso'] as $code => $name) {
            PhysicalStatus::firstOrCreate(['code' => $code], ['name' => $name]);
        }
        foreach (['available' => 'Disponible', 'assigned' => 'Asignado', 'deposit' => 'Depósito', 'missing' => 'Faltante', 'replaced' => 'Repuesto', 'revaluation' => 'En proceso de revalúo', 'retired' => 'Dado de baja'] as $code => $name) {
            AdministrativeStatus::firstOrCreate(['code' => $code], ['name' => $name]);
        }
    }
}
