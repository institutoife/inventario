<?php

namespace Database\Seeders;

use App\Models\AssetOrigin;
use Illuminate\Database\Seeder;

class AssetOriginSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['VICE-MINISTERIO' => 'VICE-MINISTERIO',
            'COMANDO-GENERAL' => 'COMANDO GENERAL',
            'COMANDO-DEPARTAMENTAL' => 'COMANDO DEPARTAMENTAL',
            'GOBIERNO-MUNICIPAL' => 'GOBIERNO MUNICIPAL',
            'GOBERNACION' => 'GOBERNACION',
            'OTBS' => 'OTBS',
            'OTROS' => 'OTROS'] as $code => $name) {
            AssetOrigin::firstOrCreate(['code' => $code], ['name' => $name, 'active' => true]);
        }
    }
}
