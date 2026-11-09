<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'CARGO-ENCARGADO-AF' => 'Encargado de Activos Fijos',
            'CARGO-RESPONSABLE-UNIDAD' => 'Responsable de Unidad',
            'CARGO-JEFE-OFICINA' => 'Jefe de Oficina',
            'CARGO-ENCARGADO-DEPOSITO' => 'Encargado de Depósito',
            'CARGO-TECNICO-ADMIN' => 'Técnico Administrativo',
            'CARGO-AUXILIAR-ADMIN' => 'Auxiliar Administrativo',
        ] as $code => $name) {
            Position::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
