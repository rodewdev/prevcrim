<?php

namespace App\Services;

use App\Models\Delito;
use App\Models\CodigoDelito;
use App\Models\Region;
use App\Models\Comuna;
use App\Models\Sector;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;

class PdfExportService
{
    /**
     * Exportar delitos filtrados a PDF
     */
    public static function exportDelitosToPdf($query, array $filtros = [], string $titulo = 'Reporte de Delitos'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            // Cargar relaciones necesarias
            $delitos = $query->with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion'])
                            ->orderBy('created_at', 'desc')
                            ->get();

            // Validar datos
            $errors = static::validatePdfData($delitos);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Preparar datos para el PDF
            $data = [
                'delitos' => $delitos,
                'titulo' => $titulo,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name,
                'institucion' => auth()->user()->institucion->nombre ?? 'Sistema',
                'filtros_aplicados' => $filtros,
                'periodo' => static::getPeriodoFromFilters($filtros),
            ];

            // Generar PDF
            $pdf = Pdf::loadView('reports.delitos-pdf', $data);

            // Configurar PDF
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions(static::getDefaultPdfOptions());

            // Notificación de éxito
            Notification::make()
                ->title('PDF generado exitosamente')
                ->body('El reporte se ha generado correctamente con ' . $delitos->count() . ' registros.')
                ->success()
                ->send();

            $filename = 'reporte_delitos_' . now()->format('Y_m_d_H_i_s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de delitos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            Notification::make()
                ->title('Error al generar PDF')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }

    /**
     * Exportar delitos seleccionados a PDF
     */
    public static function exportSelectedDelitosToPdf(Collection $delitos): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            // Cargar relaciones si no están cargadas
            $delitos = $delitos->load(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion']);

            // Validar datos
            $errors = static::validatePdfData($delitos);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            $data = [
                'delitos' => $delitos,
                'titulo' => 'Reporte de Delitos Seleccionados',
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name,
                'institucion' => auth()->user()->institucion->nombre ?? 'Sistema',
                'filtros_aplicados' => ['Selección manual' => $delitos->count() . ' registros'],
                'periodo' => 'Registros seleccionados',
            ];

            $pdf = Pdf::loadView('reports.delitos-pdf', $data);
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions(static::getDefaultPdfOptions());

            Notification::make()
                ->title('PDF generado exitosamente')
                ->body('Se han exportado ' . $delitos->count() . ' registros seleccionados.')
                ->success()
                ->send();

            $filename = 'delitos_seleccionados_' . now()->format('Y_m_d_H_i_s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de delitos seleccionados: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'delitos_count' => $delitos->count(),
            ]);

            Notification::make()
                ->title('Error al generar PDF')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }

    /**
     * Exportar colección de delitos a PDF con filtros personalizados
     */
    public static function exportDelitosCollectionToPdf(Collection $delitos, array $filtros = [], string $titulo = 'Reporte de Delitos'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            // Cargar relaciones si no están cargadas
            $delitos = $delitos->load(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion']);

            // Validar datos
            $errors = static::validatePdfData($delitos);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            $data = [
                'delitos' => $delitos,
                'titulo' => $titulo,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name,
                'institucion' => auth()->user()->institucion->nombre ?? 'Sistema',
                'filtros_aplicados' => $filtros,
                'periodo' => 'Registros seleccionados',
            ];

            // Generar PDF
            $pdf = Pdf::loadView('reports.delitos-pdf', $data);

            // Configurar PDF
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions(static::getDefaultPdfOptions());

            // Notificación de éxito
            Notification::make()
                ->title('PDF generado exitosamente')
                ->body('El reporte se ha generado correctamente con ' . $delitos->count() . ' registros.')
                ->success()
                ->send();

            $filename = 'delitos_seleccionados_' . now()->format('Y_m_d_H_i_s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de delitos seleccionados: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            Notification::make()
                ->title('Error al generar PDF')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }

    /**
     * Validar que tenemos los datos necesarios para generar el PDF
     */
    private static function validatePdfData($delitos): array
    {
        $errors = [];
        
        if ($delitos->isEmpty()) {
            $errors[] = 'No hay registros para exportar';
        }
        
        if (!auth()->check()) {
            $errors[] = 'Usuario no autenticado';
        }
        
        // Verificar que la vista existe
        if (!view()->exists('reports.delitos-pdf')) {
            $errors[] = 'Plantilla PDF no encontrada';
        }
        
        return $errors;
    }

    /**
     * Configurar opciones por defecto del PDF
     */
    private static function getDefaultPdfOptions(): array
    {
        return [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false, // Cambio por seguridad
            'defaultFont' => 'DejaVu Sans',
            'debugPng' => false,
            'debugKeepTemp' => false,
            'debugCss' => false,
            'enable_php' => false, // Seguridad
            'enable_javascript' => false, // Seguridad
            'enable_remote' => false, // Seguridad
        ];
    }

    /**
     * Aplicar filtros a la consulta
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filterName => $filterState) {
            if (empty($filterState)) continue;

            switch ($filterName) {
                case 'codigo_delito_id':
                    if (!empty($filterState['value'])) {
                        $query->where('codigo_delito_id', $filterState['value']);
                    }
                    break;

                case 'region_id':
                    if (!empty($filterState['value'])) {
                        $query->where('region_id', $filterState['value']);
                    }
                    break;

                case 'comuna_id':
                    if (!empty($filterState['value'])) {
                        $query->where('comuna_id', $filterState['value']);
                    }
                    break;

                case 'sector_id':
                    if (!empty($filterState['value'])) {
                        $query->where('sector_id', $filterState['value']);
                    }
                    break;

                case 'institucion_id':
                    if (!empty($filterState['value'])) {
                        $query->where('institucion_id', $filterState['value']);
                    }
                    break;

                case 'delincuente_id':
                    if (!empty($filterState['values'])) {
                        $query->whereHas('delincuentes', function ($q) use ($filterState) {
                            $q->whereIn('delincuentes.id', $filterState['values']);
                        });
                    }
                    break;

                case 'fecha_delito':
                    if (!empty($filterState['fecha_desde'])) {
                        $query->whereDate('fecha', '>=', $filterState['fecha_desde']);
                    }
                    if (!empty($filterState['fecha_hasta'])) {
                        $query->whereDate('fecha', '<=', $filterState['fecha_hasta']);
                    }
                    break;

                case 'fecha_registro':
                    if (!empty($filterState['registro_desde'])) {
                        $query->whereDate('created_at', '>=', $filterState['registro_desde']);
                    }
                    if (!empty($filterState['registro_hasta'])) {
                        $query->whereDate('created_at', '<=', $filterState['registro_hasta']);
                    }
                    break;
            }
        }
        
        return $query;
    }

    /**
     * Procesar filtros para mostrar en el PDF
     */
    public static function processFiltersForDisplay(array $filters): array
    {
        $filtrosAplicados = [];
        
        foreach ($filters as $filterName => $filterState) {
            if (empty($filterState)) continue;

            $label = static::getFilterLabel($filterName);
            $value = static::getFilterValue($filterName, $filterState);
            
            if ($value) {
                $filtrosAplicados[$label] = $value;
            }
        }

        return $filtrosAplicados;
    }

    /**
     * Obtener etiqueta del filtro
     */
    private static function getFilterLabel(string $filterName): string
    {
        $labels = [
            'codigo_delito_id' => 'Código de Delito',
            'region_id' => 'Región',
            'comuna_id' => 'Comuna',
            'sector_id' => 'Sector',
            'institucion_id' => 'Institución',
            'delincuente_id' => 'Delincuente',
            'fecha_delito' => 'Fecha del Delito',
            'fecha_registro' => 'Fecha de Registro',
        ];

        return $labels[$filterName] ?? ucfirst(str_replace('_', ' ', $filterName));
    }

    /**
     * Obtener valor del filtro
     */
    private static function getFilterValue(string $filterName, $filterState): string
    {
        if (is_array($filterState)) {
            if (isset($filterState['value'])) {
                // Para SelectFilter simple
                switch ($filterName) {
                    case 'codigo_delito_id':
                        $model = CodigoDelito::find($filterState['value']);
                        return $model ? $model->codigo . ' - ' . $model->descripcion : 'N/A';
                    case 'region_id':
                        $model = Region::find($filterState['value']);
                        return $model ? $model->nombre : 'N/A';
                    case 'comuna_id':
                        $model = Comuna::find($filterState['value']);
                        return $model ? $model->nombre : 'N/A';
                    case 'sector_id':
                        $model = Sector::find($filterState['value']);
                        return $model ? $model->nombre : 'N/A';
                    default:
                        return (string) $filterState['value'];
                }
            } elseif (isset($filterState['values'])) {
                // Para SelectFilter múltiple
                return count($filterState['values']) . ' elemento(s) seleccionado(s)';
            } else {
                // Para filtros de fecha
                $parts = [];
                if (!empty($filterState['fecha_desde'])) {
                    $parts[] = 'Desde: ' . $filterState['fecha_desde'];
                }
                if (!empty($filterState['fecha_hasta'])) {
                    $parts[] = 'Hasta: ' . $filterState['fecha_hasta'];
                }
                if (!empty($filterState['registro_desde'])) {
                    $parts[] = 'Desde: ' . $filterState['registro_desde'];
                }
                if (!empty($filterState['registro_hasta'])) {
                    $parts[] = 'Hasta: ' . $filterState['registro_hasta'];
                }
                return implode(', ', $parts);
            }
        }

        return (string) $filterState;
    }

    /**
     * Obtener periodo de fechas
     */
    private static function getPeriodoFromFilters(array $filters): string
    {
        $fechaDesde = null;
        $fechaHasta = null;

        if (isset($filters['fecha_delito'])) {
            $fechaDesde = $filters['fecha_delito']['fecha_desde'] ?? null;
            $fechaHasta = $filters['fecha_delito']['fecha_hasta'] ?? null;
        }

        if ($fechaDesde && $fechaHasta) {
            return "Del {$fechaDesde} al {$fechaHasta}";
        } elseif ($fechaDesde) {
            return "Desde {$fechaDesde}";
        } elseif ($fechaHasta) {
            return "Hasta {$fechaHasta}";
        }

        return 'Todos los registros';
    }
}
