<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesTableSeeder::class);
        $this->call(InstitucionesTableSeeder::class);
        $this->call(SectoresTableSeeder::class);
        $this->call(CodigosDelitosTableSeeder::class);
        $this->call(DelincuentesTableSeeder::class);
        $this->call(DelitosTableSeeder::class);
        $this->call(DelincuenteDelitoTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RegionComunaSeeder::class);
        // User::factory(10)->create();

       /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
    }
}
