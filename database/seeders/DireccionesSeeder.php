<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direccion;

class DireccionesSeeder extends Seeder
{
    public function run()
    {
        Direccion::insert([
            ['nombre' => 'Dirección 1', 'id_empresa' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dirección 2', 'id_empresa' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dirección 3', 'id_empresa' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
