<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelincuenteFamiliarTableSeeder extends Seeder
{
    public function run(): void
    {
        // Ejemplo: Asume que existen delincuentes con IDs 1, 2, 3
        DB::table('delincuente_familiar')->insert([
            [
                'delincuente_id' => 1,
                'familiar_id' => 2,
                'parentesco' => 'Hermano',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'delincuente_id' => 1,
                'familiar_id' => 3,
                'parentesco' => 'Padre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'delincuente_id' => 2,
                'familiar_id' => 3,
                'parentesco' => 'Padre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
