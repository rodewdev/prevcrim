<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PdfExportService;
use App\Models\Delito;

class TestPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf {--count=5 : Number of records to include}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PDF generation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PDF generation...');
        
        try {
            $count = $this->option('count');
            
            // Verificar que hay datos
            $totalDelitos = Delito::count();
            $this->info("Total delitos in database: {$totalDelitos}");
            
            if ($totalDelitos === 0) {
                $this->warn('No delitos found in database. Cannot test PDF generation.');
                return Command::FAILURE;
            }
            
            // Crear query de prueba
            $query = Delito::with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion'])
                           ->take($count);
            
            $this->info("Generating PDF with {$count} records...");
            
            // Simular usuario autenticado para la prueba
            $user = \App\Models\User::first();
            if (!$user) {
                $this->error('No users found. Please seed the database first.');
                return Command::FAILURE;
            }
            
            auth()->login($user);
            
            $filtros = [
                'Prueba' => 'Comando de prueba',
                'Registros' => "{$count} registros",
                'Usuario' => $user->name
            ];
            
            // Intentar generar PDF
            $result = PdfExportService::exportDelitosToPdf($query, $filtros, 'Reporte de Prueba - Comando');
            
            $this->info('✅ PDF generated successfully!');
            $this->info('📄 Response type: ' . get_class($result));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error generating PDF: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            
            if ($this->option('verbose')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
