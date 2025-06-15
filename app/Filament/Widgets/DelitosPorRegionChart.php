<?php

namespace App\Filament\Widgets;

use App\Models\Delito;
use App\Models\Region;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DelitosPorRegionChart extends ChartWidget
{
    protected static ?string $heading = 'Delitos por Región';
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    // Controlar visibilidad según rol
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }    protected function getData(): array
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

        // Obtener datos agrupados por región
        $datos = $query->select('region_id', DB::raw('count(*) as total'))
            ->whereNotNull('region_id')
            ->groupBy('region_id')
            ->orderByDesc('total')
            ->get();

        // Si no hay datos, devolver vacío
        if ($datos->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Obtener nombres de regiones y contar delitos
        $labels = [];
        $values = [];
        
        foreach ($datos as $dato) {
            $region = Region::find($dato->region_id);
            $labels[] = $region ? $region->nombre : 'Región ID: ' . $dato->region_id;
            $values[] = $dato->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de delitos',
                    'data' => $values,
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8AC249', '#EA526F', '#23395B', '#406E8E',
                        '#D499B9', '#2E294E', '#9055A2', '#2D7DD2', '#97CC04'
                    ],
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Distribución de Delitos por Región',
                ],
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
