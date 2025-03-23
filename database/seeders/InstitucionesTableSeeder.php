<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class InstitucionesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('instituciones')->insert([
            ['nombre' => 'Carabineros', 'codigo' => 'CAR001'],
            ['nombre' => 'Paz Ciudadana', 'codigo' => 'PZC002']
        ]);
    }
}
