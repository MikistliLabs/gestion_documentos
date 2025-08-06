<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoArchivo;

class TipoArchivoSeeder extends Seeder
{
    public function run()
    {
        TipoArchivo::insert([
            ['nombre' => 'PDF', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Word', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Excel', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Imagen', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
