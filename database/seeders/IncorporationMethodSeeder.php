<?php

namespace Database\Seeders;

use App\Models\IncorporationMethod;
use Illuminate\Database\Seeder;

class IncorporationMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['COMPRA' => 'Compra',
            'DONACION' => 'Donación',
            'TRANSFERENCIA' => 'Transferencia',
            'ASIGNACION' => 'Asignación',
            'COMODATO' => 'Comodato',
            'DEPOSITO-JUDICIAL' => 'Depósito judicial',
            'INCAUTACION' => 'Incautación',
            'TRANSFERENCIA-DEFINITIVA' => 'Transferencia definitiva'] as $code => $name) {
            IncorporationMethod::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
