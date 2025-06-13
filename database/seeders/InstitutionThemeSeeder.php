<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InstitutionThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear instituciones si no existen
        $instituciones = [
            ['nombre' => 'Carabineros', 'descripcion' => 'Carabineros de Chile'],
            ['nombre' => 'Paz Ciudadana', 'descripcion' => 'Fundación Paz Ciudadana'],
            ['nombre' => 'PDI', 'descripcion' => 'Policía de Investigaciones de Chile'],
        ];

        foreach ($instituciones as $inst) {
            Institucion::firstOrCreate(
                ['nombre' => $inst['nombre']],
                $inst
            );
        }

        // Crear usuarios de prueba para cada institución
        $users = [
            [
                'name' => 'Usuario Carabineros',
                'email' => 'carabineros@test.com',
                'password' => Hash::make('password'),
                'institucion_nombre' => 'Carabineros'
            ],
            [
                'name' => 'Usuario Paz Ciudadana', 
                'email' => 'paz@test.com',
                'password' => Hash::make('password'),
                'institucion_nombre' => 'Paz Ciudadana'
            ],
            [
                'name' => 'Usuario PDI',
                'email' => 'pdi@test.com', 
                'password' => Hash::make('password'),
                'institucion_nombre' => 'PDI'
            ],
        ];

        foreach ($users as $userData) {
            $institucion = Institucion::where('nombre', $userData['institucion_nombre'])->first();
            
            if ($institucion) {
                User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => $userData['password'],
                        'institucion_id' => $institucion->id,
                    ]
                );
            }
        }

        $this->command->info('✅ Instituciones y usuarios de prueba creados para testing de temas');
        $this->command->info('📧 Usuarios creados:');
        $this->command->info('   - carabineros@test.com (password)');
        $this->command->info('   - paz@test.com (password)');
        $this->command->info('   - pdi@test.com (password)');
    }
}
