<?php

namespace App\Filament\Widgets;

use App\Models\Delito;
use App\Models\Sector;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DelitosPorSectorChart extends ChartWidget
{
    protected static ?string $heading = 'Delitos por Sector de Patrullaje';
    protected static ?int $sort = 3;
    
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

        // Obtener los 15 sectores con más delitos
        $datos = $query->select('sector_id', DB::raw('count(*) as total'))
            ->whereNotNull('sector_id')
            ->groupBy('sector_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // Si no hay datos, devolver vacío
        if ($datos->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Obtener nombres de sectores y contar delitos
        $labels = [];
        $values = [];
        
        foreach ($datos as $dato) {
            $sector = Sector::find($dato->sector_id);
            $labels[] = $sector ? $sector->nombre : 'Sector ID: ' . $dato->sector_id;
            $values[] = $dato->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de delitos',
                    'data' => $values,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#2E86C1',
                    'borderWidth' => 1
                ]
            ],
            'labels' => $labels,
        ];
    }    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Top 15 Sectores con Mayor Incidencia de Delitos',
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad de Delitos'
                    ]
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Sector'
                    ]
                ]
            ],
            'indexAxis' => 'y',
        ];
    }
}
