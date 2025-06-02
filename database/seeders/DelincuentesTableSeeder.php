<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelincuentesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('delincuentes')->insert([
            [
                'rut' => '11111111-1',
                'nombre' => 'Juan',
                'apellidos' => 'Pérez',
                'alias' => 'El Lobo',
                'domicilio' => 'Calle Falsa 123',
                'ultimo_lugar_visto' => 'Plaza Central',
                'telefono_fijo' => '22223333',
                'celular' => '912345678',
                'email' => 'juan@example.com',
                'fecha_nacimiento' => '1980-01-01',
                'estado' => 'L',
                'foto' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rut' => '22222222-2',
                'nombre' => 'Pedro',
                'apellidos' => 'Gómez',
                'alias' => 'El Zorro',
                'domicilio' => 'Av. Siempre Viva 742',
                'ultimo_lugar_visto' => 'Mercado',
                'telefono_fijo' => '22224444',
                'celular' => '923456789',
                'email' => 'pedro@example.com',
                'fecha_nacimiento' => '1975-05-05',
                'estado' => 'L',
                'foto' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rut' => '33333333-3',
                'nombre' => 'Carlos',
                'apellidos' => 'Soto',
                'alias' => 'El Tigre',
                'domicilio' => 'Pasaje Los Robles 456',
                'ultimo_lugar_visto' => 'Terminal',
                'telefono_fijo' => '22225555',
                'celular' => '934567890',
                'email' => 'carlos@example.com',
                'fecha_nacimiento' => '1965-09-09',
                'estado' => 'L',
                'foto' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
