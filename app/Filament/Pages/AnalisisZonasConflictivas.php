<?php

namespace App\Filament\Pages;

use App\Models\Delito;
use App\Models\Sector;
use App\Models\Region;
use App\Models\Comuna;
use App\Models\CodigoDelito;
use App\Models\PatrullajeAsignacion;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Support\Colors\Color;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Filament\Notifications\Notification;

class AnalisisZonasConflictivas extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Análisis de Zonas Conflictivas';
    protected static ?string $title = 'Análisis de Zonas Conflictivas';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.analisis-zonas-conflictivas';
    protected static ?string $slug = 'analisis-zonas-conflictivas';
    
    // Período de tiempo seleccionado
    public $periodoSeleccionado = 'mes';
    
    // Filtros adicionales
    public $regionSeleccionada = null;
    public $comunaSeleccionada = null;
    public $tipoDelitoSeleccionado = null;
    public $mostrarSoloSinPatrullaje = false;
      // Estado para visualización del mapa
    public $mostrarMapa = false;
    public $datosMapa = [];
    
    public function mount(): void
    {
        // Verificar permisos de acceso
        abort_unless(
            auth()->user()->hasRole(['Administrador General', 'Jefe de Zona']), 
            403, 
            'No tienes permiso para acceder a esta página'
        );
        
        // Log para debugging
        \Illuminate\Support\Facades\Log::info('Montando página AnalisisZonasConflictivas para usuario: ' . auth()->user()->name);
        
        // Por defecto usar el período del último mes
        $this->periodoSeleccionado = 'mes';
        
        // Verificar si llega un parámetro de sector desde el widget
        $sectorNombre = request()->query('sector');
        if ($sectorNombre) {
            $sector = Sector::where('nombre', $sectorNombre)->first();
            if ($sector) {
                // Si el sector está en una región específica, establecer el filtro
                $this->regionSeleccionada = $sector->comuna->region_id;
                $this->comunaSeleccionada = $sector->comuna_id;
                
                \Illuminate\Support\Facades\Log::info('Filtro aplicado por sector: ' . $sectorNombre . 
                                                    ' (Región ID: ' . $this->regionSeleccionada . 
                                                    ', Comuna ID: ' . $this->comunaSeleccionada . ')');
                
                // Opcionalmente, mostrar el mapa centrado en este sector
                $this->mostrarMapa = true;
                $this->cargarDatosMapa($sector->id);
            }
        }
          // Ejecutar diagnóstico completo de delitos
        $this->diagnosticarDelitos();
        
        // Cargar datos iniciales
        $this->cargarDatos();
        
        // Diagnosticar datos de delitos PDI
        $this->diagnosticarDelitos();
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user() && auth()->user()->hasRole(['Administrador General', 'Jefe de Zona']);
    }
      public function cargarDatos()
    {
        // La carga real de datos se realiza en el método getTableQuery()
    }
    
    // Variables para usar en la vista
    public function getTotalZonas()
    {
        return $this->getTableQuery()->count();
    }
    
    public function getZonasAltoRiesgo()
    {
        $sectores = $this->getTableQuery()->get();
        $count = 0;
        
        foreach ($sectores as $sector) {
            if ($this->getIndiceNumerico($sector) >= 8) {
                $count++;
            }
        }
        
        return $count;
    }
    
    public function getPorcentajePatrullaje()
    {
        $sectores = $this->getTableQuery()->get();
        $totalZonas = count($sectores);
        $conPatrullaje = 0;
        
        foreach ($sectores as $sector) {
            if ($this->tienePatrullajeActivo($sector)) {
                $conPatrullaje++;
            }
        }
        
        return $totalZonas > 0 ? ($conPatrullaje / $totalZonas) * 100 : 0;
    }    public function getTableQuery(): Builder
    {
        // Debug para verificar los delitos de PDI
        $pdiDelitosCount = \App\Models\Delito::where('institucion_id', 2)->count();
        \Illuminate\Support\Facades\Log::info('PDI Delitos Count: ' . $pdiDelitosCount);
        
        // Mostrar delitos por sector y comuna para debug
        if ($pdiDelitosCount > 0) {
            $delitosAgrupadosPorSector = \App\Models\Delito::where('institucion_id', 2)
                ->selectRaw('sector_id, count(*) as total')
                ->groupBy('sector_id')
                ->get();
                
            foreach ($delitosAgrupadosPorSector as $grupo) {
                $sector = \App\Models\Sector::find($grupo->sector_id);
                $nombreSector = $sector ? $sector->nombre : 'Desconocido';
                $nombreComuna = $sector && $sector->comuna ? $sector->comuna->nombre : 'Desconocida';
                \Illuminate\Support\Facades\Log::info("Sector: $nombreSector (ID: {$grupo->sector_id}), Comuna: $nombreComuna, Delitos: {$grupo->total}");
            }
        }
          // Crear un subquery para filtrar los delitos correctamente
        $delitosFilteredSubQuery = Delito::query();
        
        // Log para depuración de variables de filtro
        \Illuminate\Support\Facades\Log::info('Estado de filtros: periodo=' . $this->periodoSeleccionado . 
                                           ', region=' . $this->regionSeleccionada . 
                                           ', comuna=' . $this->comunaSeleccionada . 
                                           ', tipo_delito=' . $this->tipoDelitoSeleccionado);
        
        // Aplicar filtros al subquery
        if ($this->periodoSeleccionado && $this->periodoSeleccionado != 'todo') {
            $fechaDesde = $this->getFechaDesde($this->periodoSeleccionado);
            $delitosFilteredSubQuery->where('fecha', '>=', $fechaDesde);
            \Illuminate\Support\Facades\Log::info('Aplicando filtro de fecha: ' . $fechaDesde);
        }
        
        // Aplicar filtro por institución del usuario
        if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
            $delitosFilteredSubQuery->where('institucion_id', auth()->user()->institucion_id);
        }
          // Filtro por tipo de delito
        if ($this->tipoDelitoSeleccionado) {
            $delitosFilteredSubQuery->where('codigo_delito_id', $this->tipoDelitoSeleccionado);
            \Illuminate\Support\Facades\Log::info('Aplicando filtro de tipo delito: ' . $this->tipoDelitoSeleccionado);
        }
        
        // Filtrar por región si está seleccionada
        if ($this->regionSeleccionada) {
            // Verificamos primero si la región existe
            $region = \App\Models\Region::find($this->regionSeleccionada);
            if ($region) {
                \Illuminate\Support\Facades\Log::info('Aplicando filtro de región: ' . $region->nombre . ' (ID: ' . $this->regionSeleccionada . ')');
                $delitosFilteredSubQuery->whereHas('sector.comuna.region', function ($q) {
                    $q->where('id', $this->regionSeleccionada);
                });
            }
        }
        
        // Filtrar por comuna si está seleccionada
        if ($this->comunaSeleccionada) {
            // Verificamos primero si la comuna existe
            $comuna = \App\Models\Comuna::find($this->comunaSeleccionada);
            if ($comuna) {
                \Illuminate\Support\Facades\Log::info('Aplicando filtro de comuna: ' . $comuna->nombre . ' (ID: ' . $this->comunaSeleccionada . ')');
                $delitosFilteredSubQuery->whereHas('sector', function ($q) {
                    $q->where('comuna_id', $this->comunaSeleccionada);
                });
            }
        }
          // Asegurar que la consulta tenga condiciones antes de obtener resultados
        if ($delitosFilteredSubQuery->getQuery()->wheres) {
            \Illuminate\Support\Facades\Log::info('Aplicando filtros a la consulta de delitos');
        } else {
            // Si no hay filtros específicos, asegurar que al menos filtremos por delitos existentes
            $delitosFilteredSubQuery->whereNotNull('id');
            \Illuminate\Support\Facades\Log::info('No hay filtros específicos aplicados, mostrando todos los delitos');
        }
        
        // Obtener IDs de sectores que tienen delitos con los filtros aplicados
        $sectorIdsWithDelitos = $delitosFilteredSubQuery->select('sector_id')
            ->whereNotNull('sector_id')
            ->distinct()
            ->pluck('sector_id');
        
        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Sectores con delitos encontrados: ' . count($sectorIdsWithDelitos) . 
                                           ' - IDs: ' . implode(', ', $sectorIdsWithDelitos->take(10)->toArray()) . 
                                           (count($sectorIdsWithDelitos) > 10 ? '...' : ''));
        
        // Consulta principal de sectores
        $query = Sector::query();
        
        if ($sectorIdsWithDelitos->count() > 0) {
            $query->whereIn('id', $sectorIdsWithDelitos);
        } else {
            // Si no hay filtros y no hay sectores, mostrar todos los sectores con al menos un delito
            $query->has('delitos', '>', 0);
        }
        
        $query->withCount(['delitos as total_delitos' => function ($q) {
            // Aplicar filtro por período
            if ($this->periodoSeleccionado && $this->periodoSeleccionado != 'todo') {
                $fechaDesde = $this->getFechaDesde($this->periodoSeleccionado);
                $q->where('fecha', '>=', $fechaDesde);
            }
                  // Aplicar filtro por institución del usuario
                if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                    $institucionId = auth()->user()->institucion_id;
                    if ($institucionId) {
                        $q->where('institucion_id', $institucionId);
                        \Illuminate\Support\Facades\Log::info('Aplicando filtro de institución en count: ' . $institucionId);
                    }
                }
                
                // Filtro por tipo de delito
                if ($this->tipoDelitoSeleccionado) {
                    $q->where('codigo_delito_id', $this->tipoDelitoSeleccionado);
                    \Illuminate\Support\Facades\Log::info('Aplicando filtro de tipo delito en count: ' . $this->tipoDelitoSeleccionado);                }
                
                // Filtrar por región/comuna si están seleccionados
                if ($this->regionSeleccionada) {
                    $q->where('region_id', $this->regionSeleccionada);
                }
                
                if ($this->comunaSeleccionada) {
                    $q->where('comuna_id', $this->comunaSeleccionada);
                }
            }])
            ->with(['comuna', 'comuna.region']);
            
        // Filtro por región y comuna
        if ($this->regionSeleccionada) {
            $query->whereHas('comuna', function ($q) {
                $q->where('region_id', $this->regionSeleccionada);
            });
        }
        
        if ($this->comunaSeleccionada) {
            $query->where('comuna_id', $this->comunaSeleccionada);
        }
          // Filtro para sectores sin patrullaje asignado
        if ($this->mostrarSoloSinPatrullaje) {
            $query->whereDoesntHave('patrullajesActivos');
        }
        
        // Asegurar que después de todos los filtros sigan apareciendo sectores con delitos
        $query->has('delitos', '>', 0);

        // Log para debug
        $sql = $query->toSql();
        $count = $query->count();
        \Illuminate\Support\Facades\Log::info('Consulta SQL: ' . $sql);
        \Illuminate\Support\Facades\Log::info('Total sectores filtrados: ' . $count);
        
        // Si no hay resultados, verificar si es por los filtros de región o comuna
        if ($count === 0) {
            if ($this->regionSeleccionada || $this->comunaSeleccionada) {
                \Illuminate\Support\Facades\Log::info('No hay resultados con los filtros actuales. Verificando si hay delitos sin estos filtros...');
                
                // Verificar cuántos sectores tendríamos sin los filtros de región/comuna
                $countSinFiltros = Sector::whereIn('id', $sectorIdsWithDelitos)->count();
                \Illuminate\Support\Facades\Log::info('Sectores sin filtros de región/comuna: ' . $countSinFiltros);
            }
        }
        
        $query->with(['comuna', 'comuna.region']);
        // Removed: $query->whereNotNull('comuna_id');
        // Removed debug log for comunas and regiones
        
        return $query->orderByDesc('total_delitos');
    }
    
    protected function getTableActions(): array
    {
        // No mostrar acciones (ni editar, ni ver, ni asignar)
        return [];
    }
    
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nombre')
                ->label('Sector')
                ->sortable()
                ->searchable(),
                
            Tables\Columns\TextColumn::make('comuna_delito')
                ->label('Comuna')
                ->getStateUsing(function (Sector $record) {
                    $delito = $record->delitos()->with('comuna')->orderByDesc('fecha')->first();
                    return $delito && $delito->comuna ? $delito->comuna->nombre : 'Sin comuna';
                }),
                
            Tables\Columns\TextColumn::make('region_delito')
                ->label('Región')
                ->getStateUsing(function (Sector $record) {
                    $delito = $record->delitos()->with('comuna.region')->orderByDesc('fecha')->first();
                    return $delito && $delito->comuna && $delito->comuna->region ? $delito->comuna->region->nombre : 'Sin región';
                }),
                
            Tables\Columns\TextColumn::make('total_delitos')
                ->label('Total Delitos')
                ->sortable()
                ->alignCenter(),
                
            Tables\Columns\TextColumn::make('indice_conflictividad')
                ->label('Índice de Conflictividad')
                ->getStateUsing(function (Sector $record): string {
                    return $this->calcularIndiceConflictividad($record);
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'ALTO' => 'danger',
                    'MEDIO' => 'warning',
                    'BAJO' => 'success',
                    default => 'gray',
                })
                ->alignCenter(),
                
            Tables\Columns\TextColumn::make('delito_predominante')
                ->label('Delito Predominante')
                ->getStateUsing(function (Sector $record): string {
                    return $this->getDelitoPredominante($record);
                })
                ->wrap(),
                
            Tables\Columns\TextColumn::make('tendencia')
                ->label('Tendencia')
                ->getStateUsing(function (Sector $record): string {
                    return $this->calcularTendencia($record);
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'AUMENTO' => 'danger',
                    'ESTABLE' => 'gray',
                    'DISMINUCIÓN' => 'success',
                    default => 'gray',
                })
                ->icon(fn (string $state): string => match ($state) {
                    'AUMENTO' => 'heroicon-o-arrow-trending-up',
                    'ESTABLE' => 'heroicon-o-minus',
                    'DISMINUCIÓN' => 'heroicon-o-arrow-trending-down',
                    default => '',
                })
                ->alignCenter(),
                
            Tables\Columns\TextColumn::make('estado_patrullaje')
                ->label('Estado Patrullaje')
                ->getStateUsing(function (Sector $record): string {
                    return $this->getEstadoPatrullaje($record);
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'ACTIVO' => 'success',
                    'PROGRAMADO' => 'warning',
                    'NO ASIGNADO' => 'danger',
                    default => 'gray',
                })
                ->alignCenter(),
        ];
    }
      protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('region_id')
                ->label('Región')
                ->options(function () {
                    // Registrar para depuración
                    \Illuminate\Support\Facades\Log::info('Cargando opciones de regiones');
                    
                    if (auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        // Para admin, mostrar todas las regiones
                        $regions = Region::all()->pluck('nombre', 'id')->toArray();
                        \Illuminate\Support\Facades\Log::info('Regiones disponibles para Admin: ' . count($regions));
                        return $regions;
                    } else {
                        // Para otros usuarios, filtrar por institución
                        $institucion_id = auth()->user()->institucion_id;
                        
                        // Obtener regiones que tienen delitos asociados a esta institución
                        $regions = Region::whereHas('comunas.sectores.delitos', function ($q) use ($institucion_id) {
                            $q->where('institucion_id', $institucion_id);
                        })
                        ->orWhereHas('delitos', function ($q) use ($institucion_id) {
                            $q->where('institucion_id', $institucion_id);
                        })
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                        
                        \Illuminate\Support\Facades\Log::info('Regiones disponibles para Institución ' . $institucion_id . ': ' . count($regions));
                        return $regions;
                    }                })
                ->indicateUsing(function (array $state): ?string {
                    if (! $state['value']) {
                        return null;
                    }
                    $region = Region::find($state['value']);
                    return $region ? $region->nombre : null;
                })                ->query(function (Builder $query, $state) {
                    $value = $state['value'] ?? null;
                    \Illuminate\Support\Facades\Log::info('Región seleccionada: ' . ($value ?? 'ninguna'));
                    $this->regionSeleccionada = $value;
                    $this->comunaSeleccionada = null;                    if (!$value) return $query;
                    
                    return $query->whereHas('comuna.region', function ($q) use ($value) {
                        $q->where('id', $value);
                    });
                }),
                  SelectFilter::make('comuna_id')
                ->label('Comuna')
                ->options(function () {
                    $query = Comuna::query();
                    
                    // Filtrar por región si está seleccionada
                    if ($this->regionSeleccionada) {
                        $query->where('region_id', $this->regionSeleccionada);
                    }
                    
                    // Filtrar por institución si no es admin
                    if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        $institucion_id = auth()->user()->institucion_id;
                        
                        // Si hay región seleccionada, traer todas las comunas de esa región
                        if ($this->regionSeleccionada) {
                            // No aplicar filtro adicional de institución si hay región seleccionada
                            // para asegurar que se vean todas las comunas
                        } else {
                            // Si no hay región, filtrar comunas que tienen delitos de esta institución
                            $query->whereHas('sectores.delitos', function ($q) use ($institucion_id) {
                                $q->where('institucion_id', $institucion_id);
                            })
                            ->orWhereHas('delitos', function ($q) use ($institucion_id) {
                                $q->where('institucion_id', $institucion_id);
                            });
                        }
                    }
                    
                    $comunas = $query->orderBy('nombre')->pluck('nombre', 'id')->toArray();
                    \Illuminate\Support\Facades\Log::info('Comunas disponibles: ' . count($comunas));
                    return $comunas;                })
                ->indicateUsing(function (array $state): ?string {
                    if (! $state['value']) {
                        return null;
                    }
                    $comuna = Comuna::find($state['value']);
                    return $comuna ? $comuna->nombre : null;
                })                ->query(function (Builder $query, $state) {
                    $value = $state['value'] ?? null;
                    $this->comunaSeleccionada = $value;
                    \Illuminate\Support\Facades\Log::info('Comuna seleccionada: ' . ($value ?? 'ninguna'));                    if (!$value) return $query;
                    
                    return $query->where('comuna_id', $value);
                }),
                  SelectFilter::make('tipo_delito')
                ->label('Tipo de Delito')
                ->options(function () {
                    // Consulta inicial para códigos de delito que tienen delitos registrados
                    $query = CodigoDelito::query()->whereHas('delitos');
                    
                    // Filtrar por institución si no es admin
                    if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        $institucion_id = auth()->user()->institucion_id;
                        $query->whereHas('delitos', function ($q) use ($institucion_id) {
                            $q->where('institucion_id', $institucion_id);
                        });
                    }
                    
                    // Aplicar filtros adicionales de región y comuna si están seleccionados
                    if ($this->regionSeleccionada) {
                        $query->whereHas('delitos.sector.comuna.region', function ($q) {
                            $q->where('id', $this->regionSeleccionada);
                        });
                    }
                    
                    if ($this->comunaSeleccionada) {
                        $query->whereHas('delitos', function ($q) {
                            $q->where('comuna_id', $this->comunaSeleccionada);
                        });
                    }
                    
                    $tipos = $query->orderBy('codigo')->get()
                        ->mapWithKeys(fn($codigo) => [$codigo->id => $codigo->codigo . ' - ' . $codigo->descripcion])
                        ->toArray();
                    
                    \Illuminate\Support\Facades\Log::info('Tipos de delito disponibles: ' . count($tipos));
                    return $tipos;                })
                ->indicateUsing(function (array $state): ?string {
                    if (! $state['value']) {
                        return null;
                    }
                    $codigoDelito = CodigoDelito::find($state['value']);
                    return $codigoDelito ? $codigoDelito->codigo . ' - ' . $codigoDelito->descripcion : null;
                })                ->query(function (Builder $query, $state) {
                    $value = $state['value'] ?? null;
                    $this->tipoDelitoSeleccionado = $value;
                    \Illuminate\Support\Facades\Log::info('Tipo de delito seleccionado: ' . ($value ?? 'ninguno'));                    if (!$value) return $query;
                    
                    return $query->whereHas('delitos', function ($q) use ($value) {
                        $q->where('codigo_delito_id', $value);
                    });
                }),
                  SelectFilter::make('periodo')
                ->label('Período')
                ->options([
                    'semana' => 'Última semana',
                    'mes' => 'Último mes',
                    'trimestre' => 'Último trimestre',
                    'año' => 'Último año',
                    'todo' => 'Todo el tiempo',
                ])
                ->default('todo')
                ->indicateUsing(function (array $state): ?string {
                    $periodoOptions = [
                        'semana' => 'Última semana',
                        'mes' => 'Último mes',
                        'trimestre' => 'Último trimestre',
                        'año' => 'Último año',
                        'todo' => 'Todo el tiempo',
                    ];
                    return $state['value'] ? $periodoOptions[$state['value']] : $periodoOptions['todo'];
                })
                ->query(function (Builder $query, $state) {
                    $value = $state['value'] ?? 'todo';
                    if ($value != 'todo') {
                        $fechaDesde = $this->getFechaDesde($value);
                        return $query->whereHas('delitos', function ($q) use ($fechaDesde) {
                            $q->where('fecha', '>=', $fechaDesde);
                        });
                    }
                    return $query;
                }),
            Filter::make('sin_patrullaje')
                ->label('Solo sectores sin patrullaje')
                ->query(function (Builder $query, $state) {
                    $this->mostrarSoloSinPatrullaje = $state;
                    
                    if ($state) {
                        \Illuminate\Support\Facades\Log::info('Filtrando sectores sin patrullaje');
                        
                        // Verificar si hay sectores con patrullaje para debug
                        $sectoresConPatrullaje = \App\Models\PatrullajeAsignacion::where('activo', true)
                            ->where(function($q) {
                                $q->whereNull('fecha_fin')
                                  ->orWhere('fecha_fin', '>=', now());
                            })
                            ->distinct()
                            ->pluck('sector_id')
                            ->toArray();
                            
                        \Illuminate\Support\Facades\Log::info('Sectores con patrullaje activo: ' . count($sectoresConPatrullaje));
                        
                        if (count($sectoresConPatrullaje) > 0) {
                            return $query->whereNotIn('id', $sectoresConPatrullaje);
                        } else {
                            // Si no hay sectores con patrullaje, mostrar todos los sectores
                            \Illuminate\Support\Facades\Log::info('No hay sectores con patrullaje activo');
                        }
                    }
                    
                    return $query;
                })
                ->toggle(),
        ];
    }
    
    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('exportar_pdf')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return $this->exportarPDF();
                }),
                
            Tables\Actions\Action::make('ver_mapa_calor')
                ->label('Ver Mapa de Calor')
                ->icon('heroicon-o-map')
                ->color('primary')
                ->action(function () {
                    $this->mostrarMapa = true;
                    $this->cargarDatosMapa();
                }),
        ];
    }
      public function exportarPDF()
    {
        try {
            // Obtener los datos filtrados
            $sectores = $this->getTableQuery()->get();
            
            // Procesamiento de datos para el PDF
            $zonasData = [];
            $zonasAltoRiesgo = 0;
            $conPatrullaje = 0;
            
            foreach ($sectores as $sector) {
                $indiceNumerico = $this->getIndiceNumerico($sector);
                $tendenciaPct = $this->getTendenciaPorcentaje($sector);
                $tienePatrullaje = $this->tienePatrullajeActivo($sector);

                if ($indiceNumerico >= 8) {
                    $zonasAltoRiesgo++;
                }

                if ($tienePatrullaje) {
                    $conPatrullaje++;
                }

                // Obtener comuna y región del delito más reciente
                $delito = $sector->delitos()->with('comuna.region')->orderByDesc('fecha')->first();
                $comunaNombre = $delito && $delito->comuna ? $delito->comuna->nombre : 'Sin comuna';
                $regionNombre = $delito && $delito->comuna && $delito->comuna->region ? $delito->comuna->region->nombre : 'Sin región';

                $zonasData[] = [
                    'sector' => $sector->nombre,
                    'comuna' => $comunaNombre,
                    'region' => $regionNombre,
                    'total_delitos' => $sector->total_delitos,
                    'indice' => $indiceNumerico,
                    'delito_predominante' => $this->getDelitoPredominanteSinCodigo($sector),
                    'tendencia' => $tendenciaPct,
                    'patrullaje' => $tienePatrullaje
                ];
            }
            
            // Calcular estadísticas
            $totalZonas = count($zonasData);
            $porcentajePatrullaje = $totalZonas > 0 ? ($conPatrullaje / $totalZonas) * 100 : 0;
            
            // Obtener descripción de filtros para mostrar en el PDF
            $filtrosAplicados = $this->obtenerFiltrosAplicados();
            
            // Determinar el período para mostrar en el PDF
            $periodoTexto = $this->getTextoPeriodo();
            
            // Preparar datos para el PDF
            $data = [
                'zonas' => $zonasData,
                'totalZonas' => $totalZonas,
                'zonasAltoRiesgo' => $zonasAltoRiesgo,
                'porcentajePatrullaje' => $porcentajePatrullaje,
                'filtros' => $filtrosAplicados,
                'periodo' => $periodoTexto
            ];
            
            // Generar y descargar el PDF
            return response()->streamDownload(function () use ($data) {
                echo Pdf::loadHtml(
                    Blade::render('reports.zonas-conflictivas-pdf', $data)
                )->setPaper('A4', 'landscape')->stream();
            }, 'zonas_conflictivas_' . now()->format('Y_m_d_H_i_s') . '.pdf');
              } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Error al generar el PDF')
                ->body($e->getMessage())
                ->send();
            return null;
        }
    }
    
    protected function getIndiceNumerico(Sector $sector): int
    {
        // Calcular el índice de conflictividad como un valor numérico del 1 al 10
        $totalDelitos = $sector->total_delitos ?? 0;
        $gravedad = $this->calcularGravedadDelitos($sector);
        $tendencia = $this->getTendenciaPorcentaje($sector);
        
        // Fórmula para calcular el índice (ejemplo)
        $indice = min(10, ceil(($totalDelitos * 0.4) + ($gravedad * 0.4) + (abs($tendencia) * 0.2 * ($tendencia > 0 ? 1 : 0))));
        
        return max(1, $indice); // Asegurar que el mínimo es 1
    }
    
    protected function calcularGravedadDelitos(Sector $sector): float
    {
        // Obtener los delitos del sector con su nivel de gravedad
        $delitosPorGravedad = Delito::where('sector_id', $sector->id)
            ->whereHas('codigoDelito')
            ->with('codigoDelito')
            ->get()
            ->groupBy(function($delito) {
                // Clasificar por gravedad según el código
                $codigo = $delito->codigoDelito->codigo ?? '';
                if (preg_match('/^[A-G]/', $codigo)) {
                    return 'alta'; // A-G son códigos de mayor gravedad (ejemplo)
                } elseif (preg_match('/^[H-P]/', $codigo)) {
                    return 'media';
                } else {
                    return 'baja';
                }
            })
            ->map(function($grupo) {
                return count($grupo);
            });
        
        // Ponderación por gravedad
        $gravesCount = $delitosPorGravedad['alta'] ?? 0;
        $mediosCount = $delitosPorGravedad['media'] ?? 0;
        $levesCount = $delitosPorGravedad['baja'] ?? 0;
        
        $totalPonderado = ($gravesCount * 5) + ($mediosCount * 3) + ($levesCount * 1);
        $totalDelitos = $gravesCount + $mediosCount + $levesCount;
        
        return $totalDelitos > 0 ? $totalPonderado / $totalDelitos : 0;
    }
    
    protected function getTendenciaPorcentaje(Sector $sector): int
    {
        // Calcular tendencia porcentual entre períodos
        $periodoActual = Carbon::now()->subMonth();
        $periodoAnterior = Carbon::now()->subMonths(2);
        
        $delitosActual = Delito::where('sector_id', $sector->id)
            ->where('fecha', '>=', $periodoActual)
            ->count();
        
        $delitosAnterior = Delito::where('sector_id', $sector->id)
            ->where('fecha', '<', $periodoActual)
            ->where('fecha', '>=', $periodoAnterior)
            ->count();
        
        if ($delitosAnterior == 0) {
            return $delitosActual > 0 ? 100 : 0;
        }
        
        $porcentaje = round((($delitosActual - $delitosAnterior) / $delitosAnterior) * 100);
        return $porcentaje;
    }
      protected function tienePatrullajeActivo(Sector $sector): bool
    {
        $tienePatrullaje = PatrullajeAsignacion::where('sector_id', $sector->id)
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', Carbon::now());
            })
            ->exists();
            
        // Debug para ver si tiene patrullaje
        if ($tienePatrullaje) {
            \Illuminate\Support\Facades\Log::info("Sector {$sector->id} ({$sector->nombre}) tiene patrullaje activo");
        }
            
        return $tienePatrullaje;
    }
    
    protected function getDelitoPredominanteSinCodigo(Sector $sector): string
    {
        // Consultar el tipo de delito más común en este sector (sin código)
        $delitoPredominante = Delito::where('sector_id', $sector->id)
            ->select('codigo_delito_id', DB::raw('count(*) as total'))
            ->groupBy('codigo_delito_id')
            ->orderByDesc('total')
            ->first();
        
        if ($delitoPredominante && $delitoPredominante->codigoDelito) {
            return $delitoPredominante->codigoDelito->descripcion;
        }
        
        return 'No especificado';
    }
    
    protected function obtenerFiltrosAplicados(): array
    {
        $filtros = [];
        
        if ($this->periodoSeleccionado != 'todo') {
            $opciones = [
                'semana' => 'última semana',
                'mes' => 'último mes',
                'trimestre' => 'último trimestre',
                'año' => 'último año',
            ];
            $filtros['Período'] = $opciones[$this->periodoSeleccionado] ?? $this->periodoSeleccionado;
        }
        
        if ($this->regionSeleccionada) {
            $region = Region::find($this->regionSeleccionada);
            $filtros['Región'] = $region ? $region->nombre : 'ID: ' . $this->regionSeleccionada;
        }
        
        if ($this->comunaSeleccionada) {
            $comuna = Comuna::find($this->comunaSeleccionada);
            $filtros['Comuna'] = $comuna ? $comuna->nombre : 'ID: ' . $this->comunaSeleccionada;
        }
        
        if ($this->tipoDelitoSeleccionado) {
            $tipoDelito = CodigoDelito::find($this->tipoDelitoSeleccionado);
            $filtros['Tipo de delito'] = $tipoDelito ? $tipoDelito->codigo . ' - ' . $tipoDelito->descripcion : 'ID: ' . $this->tipoDelitoSeleccionado;
        }
        
        if ($this->mostrarSoloSinPatrullaje) {
            $filtros['Estado de patrullaje'] = 'Sin patrullaje asignado';
        }
        
        return $filtros;
    }
    
    protected function getTextoPeriodo(): string
    {
        $opciones = [
            'semana' => 'Última semana',
            'mes' => 'Último mes',
            'trimestre' => 'Último trimestre',
            'año' => 'Último año',
            'todo' => 'Todo el tiempo',
        ];
        
        return $opciones[$this->periodoSeleccionado] ?? 'Período personalizado';
    }
  public function cargarDatosMapa($sectorId = null)
    {
        // Debug: Registrar la llamada a cargarDatosMapa
        \Illuminate\Support\Facades\Log::info('Cargando datos para el mapa' . ($sectorId ? " del sector ID: $sectorId" : ''));
        
        // Construir la consulta base
        $query = Delito::query();
        
        // Aplicar filtro por institución del usuario
        if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
            $institucion_id = auth()->user()->institucion_id;
            $query->where('institucion_id', $institucion_id);
            \Illuminate\Support\Facades\Log::info('Filtrado por institución: ' . $institucion_id);
        }
        
        // Filtrar por sector específico si se proporciona
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }
        
        // Aplicar filtros adicionales
        if ($this->periodoSeleccionado != 'todo') {
            $fechaDesde = $this->getFechaDesde($this->periodoSeleccionado);
            $query->where('fecha', '>=', $fechaDesde);
            \Illuminate\Support\Facades\Log::info('Filtrado por fecha desde: ' . $fechaDesde->format('Y-m-d'));
        }
        
        if ($this->regionSeleccionada) {
            $query->where('region_id', $this->regionSeleccionada);
            \Illuminate\Support\Facades\Log::info('Filtrado por región ID: ' . $this->regionSeleccionada);
        }
        
        if ($this->comunaSeleccionada) {
            $query->where('comuna_id', $this->comunaSeleccionada);
            \Illuminate\Support\Facades\Log::info('Filtrado por comuna ID: ' . $this->comunaSeleccionada);
        }
        
        if ($this->tipoDelitoSeleccionado) {
            $query->where('codigo_delito_id', $this->tipoDelitoSeleccionado);
            \Illuminate\Support\Facades\Log::info('Filtrado por tipo de delito ID: ' . $this->tipoDelitoSeleccionado);
        }
        
        // Debug: Verificar cuántos delitos se encontraron
        \Illuminate\Support\Facades\Log::info('SQL del mapa: ' . $query->toSql());
        
        // Obtener delitos
        $delitos = $query->with(['codigoDelito', 'sector.comuna.region'])
            ->get();
            
        \Illuminate\Support\Facades\Log::info('Total delitos encontrados: ' . $delitos->count());
        
        // Mapear los delitos a formato para el mapa
        $delitosData = $delitos->map(function ($delito) {
            // Usar coordenadas ficticias basadas en el ID para visualización
            $latBase = -33.447487; // Coordenada base para Santiago
            $lngBase = -70.673676;
            
            // Si es de Santiago centro, agrupar cerca del centro
            if (stripos($delito->sector->nombre ?? '', 'centro') !== false || 
                stripos($delito->sector->comuna->nombre ?? '', 'santiago') !== false) {
                $latVar = ($delito->id % 100) * 0.0002 - 0.01;
                $lngVar = ($delito->id % 50) * 0.0002;
            } else {
                // Para otros sectores, distribuir más ampliamente
                $latVar = ($delito->id % 100) * 0.001;
                $lngVar = ($delito->id % 50) * 0.001;
            }
            
            return [
                'id' => $delito->id,
                'latitud' => $latBase + $latVar,
                'longitud' => $lngBase + $lngVar,
                'fecha' => $delito->fecha->format('d/m/Y H:i'),
                'descripcion' => $delito->descripcion ?? 'Sin descripción',
                'codigo' => $delito->codigoDelito->codigo . ' - ' . $delito->codigoDelito->descripcion,
                'sector' => $delito->sector->nombre ?? 'No especificado',
                'comuna' => $delito->sector->comuna->nombre ?? 'No especificada',
                'region' => $delito->sector->comuna->region->nombre ?? 'No especificada',
                'institucion' => $delito->institucion->nombre ?? 'No especificada'
            ];
        });
        
        // Calcular límites geográficos para ajustar el mapa
        if ($delitosData->count() > 0) {
            $minLat = $delitosData->min('latitud');
            $maxLat = $delitosData->max('latitud');
            $minLng = $delitosData->min('longitud');
            $maxLng = $delitosData->max('longitud');
            
            $bounds = [
                'min_lat' => $minLat,
                'max_lat' => $maxLat,
                'min_lng' => $minLng,
                'max_lng' => $maxLng
            ];
        } else {
            // Usar Santiago como centro por defecto si no hay datos
            $bounds = [
                'min_lat' => -33.5,
                'max_lat' => -33.35,
                'min_lng' => -70.7,
                'max_lng' => -70.5
            ];
        }
        
        // Preparar datos para el mapa
        $datosMapa = [
            'delitos' => $delitosData,
            'bounds' => $bounds,
            'total' => $delitosData->count()
        ];
        
        // Guardar datos y emitir evento para que el frontend actualice el mapa
        $this->datosMapa = $datosMapa;
        $this->dispatch('mapaListo', $datosMapa);
    }
    
    protected function getDescripcionFiltros(): string
    {
        $filtros = [];
        
        if ($this->periodoSeleccionado != 'todo') {
            $opciones = [
                'semana' => 'última semana',
                'mes' => 'último mes',
                'trimestre' => 'último trimestre',
                'año' => 'último año',
            ];
            $filtros[] = 'Período: ' . ($opciones[$this->periodoSeleccionado] ?? $this->periodoSeleccionado);
        } else {
            $filtros[] = 'Período: todo el tiempo';
        }
        
        if ($this->regionSeleccionada) {
            $region = Region::find($this->regionSeleccionada);
            $filtros[] = 'Región: ' . ($region ? $region->nombre : 'ID: ' . $this->regionSeleccionada);
        }
        
        if ($this->comunaSeleccionada) {
            $comuna = Comuna::find($this->comunaSeleccionada);
            $filtros[] = 'Comuna: ' . ($comuna ? $comuna->nombre : 'ID: ' . $this->comunaSeleccionada);
        }
        
        if ($this->tipoDelitoSeleccionado) {
            $codigoDelito = CodigoDelito::find($this->tipoDelitoSeleccionado);
            $filtros[] = 'Tipo de delito: ' . ($codigoDelito ? $codigoDelito->codigo . ' - ' . $codigoDelito->descripcion : 'ID: ' . $this->tipoDelitoSeleccionado);
        }
        
        if ($this->mostrarSoloSinPatrullaje) {
            $filtros[] = 'Sólo sectores sin patrullaje';
        }
        
        return implode(', ', $filtros);
    }
    
    protected function getFechaDesde($periodo): Carbon
    {
        switch ($periodo) {
            case 'semana':
                return Carbon::now()->subWeek();
            case 'mes':
                return Carbon::now()->subMonth();
            case 'trimestre':
                return Carbon::now()->subMonths(3);
            case 'año':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subMonth(); // Por defecto, último mes
        }
    }
    
    protected function calcularIndiceConflictividad(Sector $sector): string
    {
        // Contar delitos en diferentes períodos
        $totalDelitos = $sector->total_delitos;
        
        // Calcular índice basado en cantidad de delitos
        if ($totalDelitos >= 10) {
            return 'ALTO';
        } elseif ($totalDelitos >= 5) {
            return 'MEDIO';
        } else {
            return 'BAJO';
        }
    }
    
    protected function calcularPrioridadRecomendada(Sector $sector): int
    {
        $indice = $this->calcularIndiceConflictividad($sector);
        
        // Prioridad: 1 = Alta, 2 = Media, 3 = Baja
        switch ($indice) {
            case 'ALTO':
                return 1;
            case 'MEDIO':
                return 2;
            default:
                return 3;
        }
    }
    
    protected function getDelitoPredominante(Sector $sector): string
    {
        // Consultar el tipo de delito más común en este sector
        $delitoPredominante = Delito::where('sector_id', $sector->id)
            ->select('codigo_delito_id', DB::raw('count(*) as total'))
            ->groupBy('codigo_delito_id')
            ->orderByDesc('total')
            ->first();
        
        if ($delitoPredominante && $delitoPredominante->codigoDelito) {
            return $delitoPredominante->codigoDelito->codigo . ' - ' . 
                   $delitoPredominante->codigoDelito->descripcion;
        }
        
        return 'No especificado';
    }
    
    protected function calcularTendencia(Sector $sector): string
    {
        // Calcular tendencia comparando períodos
        $periodoActual = Carbon::now()->subMonth();
        $periodoAnterior = Carbon::now()->subMonths(2);
        
        $delitosActual = Delito::where('sector_id', $sector->id)
            ->where('fecha', '>=', $periodoActual)
            ->count();
        
        $delitosAnterior = Delito::where('sector_id', $sector->id)
            ->where('fecha', '<', $periodoActual)
            ->where('fecha', '>=', $periodoAnterior)
            ->count();
        
        if ($delitosActual > $delitosAnterior * 1.2) { // 20% más
            return 'AUMENTO';
        } elseif ($delitosActual < $delitosAnterior * 0.8) { // 20% menos
            return 'DISMINUCIÓN';
        } else {
            return 'ESTABLE';
        }
    }
      protected function getEstadoPatrullaje(Sector $sector): string
    {
        // Verificar si hay patrullajes activos para este sector
        $patrullajeActivo = PatrullajeAsignacion::where('sector_id', $sector->id)
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', Carbon::now());
            })
            ->orderBy('prioridad') // Ordenar por prioridad (1=Alta, 2=Media, 3=Baja)
            ->first();
        
        if ($patrullajeActivo) {
            return 'ACTIVO';
        }
        
        // Verificar si hay patrullajes programados para el futuro
        $patrullajeProgramado = PatrullajeAsignacion::where('sector_id', $sector->id)
            ->where('activo', true)
            ->where('fecha_inicio', '>', Carbon::now())
            ->first();
            
        if ($patrullajeProgramado) {
            return 'PROGRAMADO';
        }
        
        return 'NO ASIGNADO';
    }
    
    // El método getTableHeaderActions() ya existe, por lo que se eliminó esta duplicación
    
    protected function getTableEmptyStateActions(): array
    {
        return [];
    }
    
    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No se encontraron sectores conflictivos';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Ajusta los filtros para ver resultados.';
    }
      protected function getViewData(): array
    {
        return [
            'totalZonas' => $this->getTotalZonas(),
            'zonasAltoRiesgo' => $this->getZonasAltoRiesgo(),
            'porcentajePatrullaje' => $this->getPorcentajePatrullaje(),
            'table' => $this->getTable(),
        ];
    }
    
    public function getTable(): Table
    {
        return Table::make($this)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->headerActions($this->getTableHeaderActions())
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->emptyStateDescription($this->getTableEmptyStateDescription());
    }
    
    // Método para diagnóstico - se puede llamar desde mount() o desde otros métodos para verificar datos
    protected function diagnosticarDelitos(): void
    {
        $delitosPDI = \App\Models\Delito::where('institucion_id', 2)->get();
        
        \Illuminate\Support\Facades\Log::info('DIAGNÓSTICO DE DELITOS PDI:');
        \Illuminate\Support\Facades\Log::info('Total delitos PDI: ' . $delitosPDI->count());
        
        foreach ($delitosPDI as $delito) {
            $sectorNombre = $delito->sector ? $delito->sector->nombre : 'Sin sector';
            $comunaNombre = $delito->comuna ? $delito->comuna->nombre : 'Sin comuna';
            $regionNombre = $delito->region ? $delito->region->nombre : 'Sin región';
            $codigoDelito = $delito->codigoDelito ? $delito->codigoDelito->codigo : 'Sin código';
            
            \Illuminate\Support\Facades\Log::info("Delito ID: {$delito->id}, Fecha: {$delito->fecha}, ".
                                                "Sector: {$sectorNombre}, Comuna: {$comunaNombre}, ".
                                                "Región: {$regionNombre}, Código: {$codigoDelito}");
        }
        
        // Verificar regiones disponibles
        $regiones = \App\Models\Region::all();
        \Illuminate\Support\Facades\Log::info('Total regiones: ' . $regiones->count());
        
        // Verificar comunas disponibles
        $comunas = \App\Models\Comuna::all();
        \Illuminate\Support\Facades\Log::info('Total comunas: ' . $comunas->count());
        
        // Verificar sectores disponibles
        $sectores = \App\Models\Sector::all();
        \Illuminate\Support\Facades\Log::info('Total sectores: ' . $sectores->count());
        
        // Verificar sectores con delitos de PDI
        $sectoresConDelitosPDI = \App\Models\Sector::whereHas('delitos', function($q) {
            $q->where('institucion_id', 2);
        })->get();
        
        \Illuminate\Support\Facades\Log::info('Sectores con delitos de PDI: ' . $sectoresConDelitosPDI->count());
        
        foreach ($sectoresConDelitosPDI as $sector) {
            $totalDelitos = $sector->delitos()->where('institucion_id', 2)->count();
            \Illuminate\Support\Facades\Log::info("Sector: {$sector->nombre}, ID: {$sector->id}, Comuna: {$sector->comuna->nombre}, Total delitos PDI: {$totalDelitos}");
        }
    }
}
