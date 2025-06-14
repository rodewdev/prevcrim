<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Models\Delincuente;

Route::get('/admin/resources/delincuentes/{record}/delitos', function ($record) {
    $delincuente = Delincuente::findOrFail($record);
    return view('filament.resources.delincuente-resource.pages.delitos-modal', [
        'delincuente' => $delincuente
    ]);
})->name('filament.admin.resources.delincuentes.delitos');

// Ruta de prueba para PDF (solo en desarrollo)
if (app()->environment('local')) {
    require __DIR__ . '/test-pdf.php';
    require __DIR__ . '/test-simple.php';
}
