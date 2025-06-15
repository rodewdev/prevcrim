<?php

namespace App\Filament\Widgets;

use App\Models\Delito;
use App\Models\Sector;
use App\Models\PatrullajeAsignacion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SectoresConflictivos extends ChartWidget
{
    protected static ?string $heading = 'Sectores Conflictivos';
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    // Opciones de periodo para el filtro
    public function getPeriodoOptions(): array
    {
        return [
            'semana' => 'Última semana',
            'mes' => 'Último mes',
            'trimestre' => 'Último trimestre',
            'año' => 'Último año',
            'todo' => 'Todo el tiempo',
        ];
    }
    
    protected function getFilters(): ?array
    {
        return $this->getPeriodoOptions();
    }
    
    // Control de visibilidad por rol
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Administrador General', 'Jefe de Zona']);
    }
    
    protected function getData(): array
    {
        $user = auth()->user();
        
        // Verificar que el usuario existe y tiene institución (si no es admin)
        if (!$user) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        $query = Delito::query();
        
        // Si no es admin, solo ve datos de su institución
        if (!$user->hasRole('Administrador General')) {
            if (!$user->institucion_id) {
                // Usuario sin institución no ve datos
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }
            $query->where('institucion_id', $user->institucion_id);
        }
        
        // Aplicar filtro de periodo
        $fechaDesde = null;
        $filtroActual = $this->filter ?? 'mes'; // Usar $this->filter en lugar de $this->getFilter()
        
        switch ($filtroActual) {
            case 'semana':
                $fechaDesde = Carbon::now()->subWeek();
                break;
            case 'mes':
                $fechaDesde = Carbon::now()->subMonth();
                break;
            case 'trimestre':
                $fechaDesde = Carbon::now()->subMonths(3);
                break;
            case 'año':
                $fechaDesde = Carbon::now()->subYear();
                break;
        }
        
        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        
        // Obtener los 10 sectores más conflictivos
        $datos = $query->select('sector_id', DB::raw('count(*) as total_delitos'))
            ->whereNotNull('sector_id')
            ->groupBy('sector_id')
            ->orderByDesc('total_delitos')
            ->limit(10)
            ->get();
        
        // Si no hay datos, devolver vacío
        if ($datos->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        // Obtener info de patrullajes asignados a estos sectores
        $sectorIds = $datos->pluck('sector_id')->toArray();
        $patrullajesActivos = PatrullajeAsignacion::whereIn('sector_id', $sectorIds)
            ->where('activo', true)
            ->where(function($q) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', Carbon::now());
            })
            ->pluck('sector_id')
            ->toArray();
        
        // Preparar datos para el gráfico
        $labels = [];
        $delitosValues = [];
        $colors = [];
        
        foreach ($datos as $dato) {
            $sector = Sector::find($dato->sector_id);
            $sectorNombre = $sector ? $sector->nombre : 'Sector ID: ' . $dato->sector_id;
            
            // Añadir indicador si tiene patrullaje activo
            $tienePatrullaje = in_array($dato->sector_id, $patrullajesActivos);
            $indicadorPatrullaje = $tienePatrullaje ? ' 🚨' : '';
            $labels[] = $sectorNombre . $indicadorPatrullaje;
            
            $delitosValues[] = $dato->total_delitos;
            
            // Color rojo para sectores sin patrullaje, verde para sectores con patrullaje
            $colors[] = $tienePatrullaje ? '#22c55e' : '#ef4444'; // Verde o Rojo
        }
        
        // Generar tooltips personalizados
        $tooltips = [];
        foreach ($datos as $index => $dato) {
            $sector = Sector::find($dato->sector_id);
            $tienePatrullaje = in_array($dato->sector_id, $patrullajesActivos);
            $statusText = $tienePatrullaje ? 'Con patrullaje asignado' : 'Sin patrullaje asignado';
            
            $tooltips[] = [
                'sector' => $sector ? $sector->nombre : 'Sector ID: ' . $dato->sector_id,
                'delitos' => $dato->total_delitos,
                'status' => $statusText
            ];
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Número de delitos',
                    'data' => $delitosValues,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
            'tooltips' => $tooltips,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let tooltipData = context.chart.data.tooltips[context.dataIndex];
                            return [
                                "Delitos: " + tooltipData.delitos,
                                "Estado: " + tooltipData.status,
                                "Click para ver detalles"
                            ];
                        }',
                    ],
                ],
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Número de delitos'
                    ],
                    'beginAtZero' => true,
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Sectores (🚨 = patrullaje asignado)'
                    ],
                    'ticks' => [
                        'autoSkip' => false,
                        'maxRotation' => 45,
                        'minRotation' => 45
                    ]
                ],
            ],
            'onClick' => 'function(e, activeElements) {
                if (activeElements.length > 0) {
                    const index = activeElements[0].index;
                    const sectorName = this.data.labels[index];
                    // Redirigir a la página de análisis detallado
                    window.location.href = "/admin/analisis-zonas-conflictivas?sector=" + encodeURIComponent(sectorName);
                }
            }',
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
