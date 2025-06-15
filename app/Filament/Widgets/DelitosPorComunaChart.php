<?php

namespace App\Filament\Widgets;

use App\Models\Delito;
use App\Models\Comuna;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DelitosPorComunaChart extends ChartWidget
{
    protected static ?string $heading = 'Delitos por Comuna';
    protected static ?int $sort = 1;
    
    // Permitir que el widget ocupe más espacio
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

        // Obtener las 10 comunas con más delitos
        $datos = $query->select('comuna_id', DB::raw('count(*) as total'))
            ->whereNotNull('comuna_id')
            ->groupBy('comuna_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Si no hay datos, devolver vacío
        if ($datos->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Obtener nombres de comunas y contar delitos
        $labels = [];
        $values = [];
        
        foreach ($datos as $dato) {
            $comuna = Comuna::find($dato->comuna_id);
            $labels[] = $comuna ? $comuna->nombre : 'Comuna ID: ' . $dato->comuna_id;
            $values[] = $dato->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de delitos',
                    'data' => $values,
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8AC249', '#EA526F', '#23395B', '#406E8E'
                    ],
                ]
            ],
            'labels' => $labels,
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
                'title' => [
                    'display' => true,
                    'text' => 'Top 10 Comunas con Mayor Incidencia de Delitos',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad de Delitos'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Comuna'
                    ]
                ]
            ],
        ];
    }
}
