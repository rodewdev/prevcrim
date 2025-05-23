<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitucionesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('instituciones')->insert([
            ['nombre' => 'Carabineros', 'codigo' => 'CAR001'],
            ['nombre' => 'Paz Ciudadana', 'codigo' => 'PZC002'],
            ['nombre' => 'Policia de Investigaciones', 'codigo' => 'PDI003']
        ]);
    }
}
