<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Jefe de Zona', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);
    }
}
