<?php

namespace Database\Seeders;

use App\Models\BudgetItem;
use Illuminate\Database\Seeder;

class BudgetItemSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'REF-COMPUTACION' => 'Equipos de Computación',
            'REF-COMUNICACION' => 'Equipos de Comunicación',
            'REF-EDUCACIONAL' => 'Equipo Educacional y Recreativo',
            'REF-MEDICO' => 'Equipo Médico y de Laboratorio',
            'REF-OFICINA' => 'Equipo de Oficina y Muebles',
            'REF-MAQUINARIA' => 'Otra Maquinaria y Equipo',
            'REF-INTANGIBLES' => 'Activos Intangibles',
            'REF-OTROS' => 'Otros Activos',
            'REF-VEHICULOS' => 'Vehículos y Equipo de Transporte',
        ] as $code => $name) {
            BudgetItem::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
