<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectores = [
            'Sector Norte',
            'Sector Poniente',
            'Sector Sur',
            'Sector Oriente',
            'Sector Centro',
            'Sector Occidente',
            'Sector Este',
        ];

        foreach ($sectores as $sector) {
            DB::table('sectores')->insert([
                'nombre' => $sector,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
