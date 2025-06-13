<?php

namespace App\Filament\Widgets;

use App\Models\Delito;
use App\Models\Comuna;
use App\Models\Region;
use App\Models\Sector;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class GraficoIntegradoDelitos extends Widget
{    protected static ?string $heading = 'Análisis de Delitos';
    protected static ?int $sort = 0;
    
    // Permitir que el widget ocupe todo el ancho
    protected int | string | array $columnSpan = 'full';
    
    // Controlar visibilidad según rol
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }

    public $tipoGrafico = 'comuna';
    public $tipoVisualizacion = 'bar';
    public $limiteDatos = 10;
    public $datos = [];
    public $etiquetas = [];
    public $titulo = 'Análisis de Delitos'; // Propiedad no estática para título dinámico
    public $totales = [];
    public $colores = [];

    public function mount()
    {
        $this->actualizarGrafico();
    }

    public function actualizarGrafico()
    {
        $user = auth()->user();
        $query = Delito::query();
        
        // Si no es admin, solo ve datos de su institución
        if (!$user->hasRole('Administrador General')) {
            $query->where('institucion_id', $user->institucion_id);
        }
        
        // Limpiar datos anteriores
        $this->etiquetas = [];
        $this->totales = [];

        // Obtener datos según selección
        switch ($this->tipoGrafico) {
            case 'comuna':
                $datos = $query->select('comuna_id as id', DB::raw('count(*) as total'))
                    ->groupBy('comuna_id')
                    ->orderByDesc('total')
                    ->limit($this->limiteDatos)
                    ->get();                foreach ($datos as $dato) {
                    $modelo = Comuna::find($dato->id);
                    $this->etiquetas[] = $modelo ? $modelo->nombre : 'ID: ' . $dato->id;
                    $this->totales[] = $dato->total;
                }
                
                $this->titulo = 'Delitos por Comuna';
                break;

            case 'region':
                $datos = $query->select('region_id as id', DB::raw('count(*) as total'))
                    ->groupBy('region_id')
                    ->orderByDesc('total')
                    ->limit($this->limiteDatos)
                    ->get();                foreach ($datos as $dato) {
                    $modelo = Region::find($dato->id);
                    $this->etiquetas[] = $modelo ? $modelo->nombre : 'ID: ' . $dato->id;
                    $this->totales[] = $dato->total;
                }
                
                $this->titulo = 'Delitos por Región';
                break;

            case 'sector':
                $datos = $query->select('sector_id as id', DB::raw('count(*) as total'))
                    ->whereNotNull('sector_id')
                    ->groupBy('sector_id')
                    ->orderByDesc('total')
                    ->limit($this->limiteDatos)
                    ->get();                foreach ($datos as $dato) {
                    $modelo = Sector::find($dato->id);
                    $this->etiquetas[] = $modelo ? $modelo->nombre : 'ID: ' . $dato->id;
                    $this->totales[] = $dato->total;
                }
                
                $this->titulo = 'Delitos por Sector de Patrullaje';
                break;
        }

        // Generar colores para el gráfico
        $this->colores = $this->generarColores(count($this->totales));

        // Preparar datos para el gráfico
        $this->datos = [
            'labels' => $this->etiquetas,
            'datasets' => [
                [
                    'label' => 'Cantidad de delitos',
                    'data' => $this->totales,
                    'backgroundColor' => $this->colores,
                    'borderColor' => $this->colores,
                    'borderWidth' => 1,
                ]
            ]
        ];
        
        // Emitir evento con los datos actualizados para el JS
        $this->dispatch('graficoActualizado', [
            'datos' => json_encode($this->datos),
            'tipoVisualizacion' => $this->tipoVisualizacion
        ]);
    }

    private function generarColores($cantidad)
    {
        $coloresBase = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#8AC249', '#EA526F', '#23395B', '#406E8E',
            '#D499B9', '#2E294E', '#9055A2', '#2D7DD2', '#97CC04',
            '#EF476F', '#FFD166', '#06D6A0', '#118AB2', '#073B4C'
        ];

        $colores = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $colores[] = $coloresBase[$i % count($coloresBase)];
        }
        
        return $colores;
    }    protected function getViewData(): array
    {
        return [
            'datos' => json_encode($this->datos),
            'tipoGrafico' => $this->tipoGrafico,
            'tipoVisualizacion' => $this->tipoVisualizacion,
            'limiteDatos' => $this->limiteDatos,
            'titulo' => $this->titulo,
        ];
    }
    
    protected static string $view = 'filament.widgets.grafico-integrado-delitos';
}
