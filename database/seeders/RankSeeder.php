<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'GRAL-SUP' => 'General Superior',
            'GRAL-MAY' => 'General Mayor',
            'GRAL-PRI' => 'General Primero',
            'CNL' => 'Coronel',
            'TCNL' => 'Teniente Coronel',
            'MY' => 'Mayor',
            'CAP' => 'Capitán',
            'TTE' => 'Teniente',
            'SBTTE' => 'Subteniente',
            'SOF-SUP' => 'Suboficial Superior',
            'SOF-MAY' => 'Suboficial Mayor',
            'SOF-PRI' => 'Suboficial Primero',
            'SOF-SEG' => 'Suboficial Segundo',
            'SGTO-MAY' => 'Sargento Mayor',
            'SGTO-PRI' => 'Sargento Primero',
            'SGTO-SEG' => 'Sargento Segundo',
            'SGTO' => 'Sargento',
            'CNL-SERV' => 'Coronel de Servicios',
            'TCNL-SERV' => 'Teniente Coronel de Servicios',
            'MY-SERV' => 'Mayor de Servicios',
            'CAP-SERV' => 'Capitán de Servicios',
            'TTE-SERV' => 'Teniente de Servicios',
            'SBTTE-SERV' => 'Subteniente de Servicios',
            'SOF-SUP-SERV' => 'Suboficial Superior de Servicios',
            'SOF-MAY-SERV' => 'Suboficial Mayor de Servicios',
            'SOF-PRI-SERV' => 'Suboficial Primero de Servicios',
            'SOF-SEG-SERV' => 'Suboficial Segundo de Servicios',
            'SGTO-MAY-SERV' => 'Sargento Mayor de Servicios',
            'SGTO-PRI-SERV' => 'Sargento Primero de Servicios',
            'SGTO-SEG-SERV' => 'Sargento Segundo de Servicios',
            'SGTO-SERV' => 'Sargento de Servicios',
        ] as $code => $name) {
            Rank::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
