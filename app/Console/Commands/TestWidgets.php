<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Delito;
use App\Models\Institucion;

class TestWidgets extends Command
{
    protected $signature = 'test:widgets';
    protected $description = 'Test widget data filtering by institution';

    public function handle()
    {
        $this->info('Testing Widget Data Filtering...');
        
        // Test data counts
        $this->info('=== General Data ===');
        $this->info('Total usuarios: ' . User::count());
        $this->info('Total delitos: ' . Delito::count());
        $this->info('Total instituciones: ' . Institucion::count());
        
        $this->info('=== Users with Institution ===');
        $users = User::with('institucion')->whereNotNull('institucion_id')->get();
        foreach ($users as $user) {
            $this->info("Usuario: {$user->name} - Institución: {$user->institucion->nombre}");
        }
        
        $this->info('=== Delitos by Institution ===');
        $instituciones = Institucion::withCount('delitos')->get();
        foreach ($instituciones as $institucion) {
            $this->info("Institución: {$institucion->nombre} - Delitos: {$institucion->delitos_count}");
        }
        
        $this->info('=== Testing Widget Logic ===');
        
        // Test for each user
        foreach ($users as $user) {
            $this->info("--- Testing for user: {$user->name} ({$user->institucion->nombre}) ---");
            
            // Simulate user login
            auth()->login($user);
            
            $query = Delito::query();
            
            // Apply institution filter
            if (!$user->hasRole('Administrador General')) {
                $query->where('institucion_id', $user->institucion_id);
            }
            
            $delitosCount = $query->count();
            $this->info("Delitos visibles para este usuario: {$delitosCount}");
            
            // Test by comuna
            $comunasData = $query->select('comuna_id', \DB::raw('count(*) as total'))
                ->whereNotNull('comuna_id')
                ->groupBy('comuna_id')
                ->limit(5)
                ->get();
            
            $this->info("Comunas con delitos (top 5):");
            foreach ($comunasData as $data) {
                $comuna = \App\Models\Comuna::find($data->comuna_id);
                $this->info("  - {$comuna->nombre}: {$data->total} delitos");
            }
        }
        
        auth()->logout();
        $this->info('Test completed!');
    }
}
