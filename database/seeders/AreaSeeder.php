<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run()
    {
        Area::insert([
            ['nombre' => 'Área A', 'id_direccion' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Área B', 'id_direccion' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Área C', 'id_direccion' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
