<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Asegúrate de importar tu modelo User

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin General',
            'email' => 'admin@sipc.com',
            'password' => Hash::make('123456'),
            'institucion_id' => 1
        ]);

       
        $user->assignRole('Administrador General');
    }
}