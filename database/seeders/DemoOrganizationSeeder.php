<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Office;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $root = OrganizationalUnit::firstOrCreate(['code' => 'DEMO-INSTITUCION'], ['name' => 'Institución de prueba', 'active' => true]);
            foreach ([
                'ADMINISTRACION' => ['Dirección administrativa de prueba', ['ACTIVOS' => 'Oficina de activos fijos de prueba', 'CONTABILIDAD' => 'Oficina de contabilidad de prueba']],
                'OPERACIONES' => ['Dirección de operaciones de prueba', ['OPERACIONES' => 'Oficina de operaciones de prueba', 'LOGISTICA' => 'Oficina de logística de prueba']],
                'TECNOLOGIA' => ['Unidad de tecnología de prueba', ['SISTEMAS' => 'Oficina de sistemas de prueba']],
            ] as $code => [$name, $offices]) {
                $unit = OrganizationalUnit::firstOrCreate(['code' => 'DEMO-'.$code], ['name' => $name, 'parent_id' => $root->id, 'active' => true]);
                foreach ($offices as $officeCode => $officeName) {
                    $office = Office::firstOrCreate(['code' => 'DEMO-OF-'.$officeCode], ['name' => $officeName, 'organizational_unit_id' => $unit->id, 'active' => true]);
                    Location::firstOrCreate(['code' => 'DEMO-LOC-'.$officeCode], ['name' => 'Ambiente de prueba · '.$officeName, 'office_id' => $office->id, 'is_deposit' => false, 'active' => true]);
                }
            }
            $office = Office::where('code', 'DEMO-OF-ACTIVOS')->sole();
            Location::firstOrCreate(['code' => 'DEMO-DEPOSITO'], ['name' => 'Depósito de prueba', 'office_id' => $office->id, 'is_deposit' => true, 'active' => true]);
            foreach (['ENCARGADO-AF' => 'Encargado de activos fijos', 'RESPONSABLE' => 'Responsable de oficina', 'TECNICO' => 'Técnico de apoyo'] as $code => $name) {
                Position::firstOrCreate(['code' => 'DEMO-'.$code], ['name' => $name.' (prueba)', 'active' => true]);
            }
        });
    }
}
