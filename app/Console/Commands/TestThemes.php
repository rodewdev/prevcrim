<?php

namespace App\Console\Commands;

use App\Services\ThemeService;
use Illuminate\Console\Command;

class TestThemes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'theme:test {institution?}';

    /**
     * The console command description.
     */
    protected $description = 'Probar los temas institucionales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $institution = $this->argument('institution');

        if (!$institution) {
            $this->info('Temas disponibles:');
            $this->table(
                ['Institución', 'Clase CSS', 'Color Primario'],
                [
                    ['Carabineros', 'theme-carabineros', '#2E6B36'],
                    ['Paz Ciudadana', 'theme-paz-ciudadana', '#002060'],
                    ['PDI', 'theme-pdi', '#003366'],
                ]
            );
            return;
        }

        $theme = ThemeService::getThemeForInstitution($institution);
        
        $this->info("Tema para: {$institution}");
        $this->info("Clase CSS: " . ThemeService::getThemeClassName($institution));
        $this->info("Icono: " . ThemeService::getInstitutionIcon($institution));
        
        $this->table(
            ['Variable', 'Valor'],
            [
                ['Primary', $theme['primary']],
                ['Primary Hover', $theme['primary_hover']],
                ['Secondary', $theme['secondary']],
                ['Accent', $theme['accent']],
                ['Success', $theme['success']],
                ['Warning', $theme['warning']],
                ['Danger', $theme['danger']],
                ['Info', $theme['info']],
            ]
        );
    }
}
