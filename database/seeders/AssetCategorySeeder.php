<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'CAT-COMPUTACION' => 'Equipos de Computación',
            'CAT-COMUNICACION' => 'Equipos de Comunicación',
            'CAT-EDUCACIONAL' => 'Equipo Educacional y Recreativo',
            'CAT-MEDICO' => 'Equipo Médico y de Laboratorio',
            'CAT-OFICINA' => 'Equipo de Oficina y Muebles',
            'CAT-MAQUINARIA' => 'Otra Maquinaria y Equipo',
            'CAT-INTANGIBLES' => 'Activos Intangibles',
            'CAT-OTROS' => 'Otros Activos',
            'CAT-VEHICULOS' => 'Vehículos y Equipo de Transporte',
        ] as $code => $name) {
            AssetCategory::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
