<?php

use Illuminate\Support\Facades\Route;
use App\Services\PdfExportService;
use App\Models\Delito;
use App\Models\User;

Route::get('/test-pdf', function () {
    try {
        // Simular usuario autenticado
        $user = User::first();
        if (!$user) {
            return response()->json(['error' => 'No users found'], 500);
        }
        auth()->login($user);
        
        // Verificar que hay delitos
        $totalDelitos = Delito::count();
        if ($totalDelitos === 0) {
            return response()->json(['error' => 'No delitos found'], 500);
        }
        
        // Obtener algunos delitos de prueba
        $query = Delito::with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion'])
                      ->take(5);
        
        $filtros = [
            'Prueba' => 'Reporte de prueba',
            'Registros' => '5 primeros registros',
            'Usuario' => $user->name
        ];
        
        return PdfExportService::exportDelitosToPdf($query, $filtros, 'Reporte de Prueba');
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/test-pdf-simple', function () {
    try {
        // Verificar que DomPDF funciona con una vista simple
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Test PDF</h1><p>This is a simple test.</p>');
        return $pdf->download('test.pdf');
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/test-pdf-debug', function () {
    try {
        // Simular usuario autenticado
        $user = User::first();
        if (!$user) {
            return response()->json(['error' => 'No users found'], 500);
        }
        auth()->login($user);
        
        // Obtener un delito para debug
        $delito = Delito::with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion'])
                       ->first();
        
        if (!$delito) {
            return response()->json(['error' => 'No delitos found'], 500);
        }
        
        // Debug de la relación
        $debug = [
            'delito_id' => $delito->id,
            'delincuentes_count' => $delito->delincuentes ? $delito->delincuentes->count() : 0,
            'delincuentes_type' => get_class($delito->delincuentes),
            'delincuentes_data' => $delito->delincuentes ? $delito->delincuentes->toArray() : null,
        ];
        
        return response()->json($debug);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});
