<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresasSeeder extends Seeder
{
    public function run()
    {
        Empresa::insert([
            ['nombre' => 'Empresa A', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empresa B', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empresa C', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
