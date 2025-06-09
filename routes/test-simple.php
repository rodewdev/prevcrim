<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

// Ruta para probar DomPDF básico
Route::get('/test-dompdf', function () {
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('
            <html>
            <head>
                <style>
                    body { font-family: DejaVu Sans, sans-serif; }
                </style>
            </head>
            <body>
                <h1>Prueba PDF</h1>
                <p>Este es un PDF de prueba básico.</p>
                <p>Fecha: ' . now()->format('d/m/Y H:i:s') . '</p>
            </body>
            </html>
        ');
        
        return $pdf->download('test-basic.pdf');
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
});

// Ruta para probar la vista Blade sin datos
Route::get('/test-view', function () {
    try {
        // Crear datos de prueba vacíos
        $data = [
            'titulo' => 'Reporte de Prueba',
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'usuario' => 'Usuario de Prueba',
            'institucion' => 'Institución de Prueba',
            'filtros_aplicados' => ['Test' => 'Sin filtros'],
            'periodo' => 'Prueba',
            'delitos' => collect([]) // Colección vacía
        ];
        
        return view('reports.delitos-pdf', $data);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
});
