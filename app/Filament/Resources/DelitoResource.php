<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelitoResource\Pages;
use App\Models\Delito;
use App\Models\Sector;
use App\Models\CodigoDelito;
use App\Models\Region;
use App\Models\Comuna;
use App\Models\Institucion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use App\Services\PdfExportService;
use Illuminate\Support\Facades\Blade; 

class DelitoResource extends Resource
{
    protected static ?string $model = Delito::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

public static function canViewAny(): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
}
public static function canCreate(): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Operador']);
}
public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Operador']);
}
public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
{
    return false;
}
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('delincuente_id')
                ->label('Delincuente (RUT)')
                ->options(function () {
                    return \App\Models\Delincuente::all()->mapWithKeys(function ($delincuente) {
                        return [$delincuente->id => $delincuente->rut . ' - ' . $delincuente->nombre . ' ' . $delincuente->apellidos];
                    });
                })
                ->searchable()
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ]),
            Forms\Components\Select::make('codigo_delito_id')
                ->label('Código de Delito')
                ->options(function () {
                    return CodigoDelito::pluck('codigo', 'id')->map(function ($codigo, $id) {
                        $codigoDelito = CodigoDelito::find($id);
                        return $codigo . ' - ' . $codigoDelito->descripcion;
                    });
                })
                ->searchable()
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    if ($state) {
                        $codigoDelito = CodigoDelito::find($state);
                        $set('descripcion', $codigoDelito->descripcion);
                    }
                }),
            Forms\Components\Textarea::make('descripcion')
                ->label('Descripción')
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->maxLength(1000),
            Forms\Components\TextInput::make('ubicacion')
                ->label('Ubicación del Delito')
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->live()
                ->readonly(),
            Forms\Components\ViewField::make('ubicacion_mapa')
                ->view('filament.custom.address-map-field', [
                    'id' => 'ubicacion',
                    'label' => 'Ubicación (mapa)',
                    'addressField' => 'ubicacion',
                ]),
            Forms\Components\Select::make('region_id')
                ->label('Región')
                ->options(Region::pluck('nombre', 'id'))
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->live(),
            Forms\Components\Select::make('comuna_id')
                ->label('Comuna')
                ->options(function (Forms\Get $get) {
                    $regionId = $get('region_id');
                    if (!$regionId) {
                        return Comuna::pluck('nombre', 'id');
                    }
                    return Comuna::where('region_id', $regionId)->pluck('nombre', 'id');
                })
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('sector_id')
                ->label('Sector')
                ->relationship('sector', 'nombre')
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ]),
            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha del Delito')
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

    // Si el usuario es Admin General, muestra todo
        if (auth()->user()->hasRole('Administrador General') || auth()->user()->hasRole('Super Admin')) {
            return $query;
    }

    // Si NO, solo muestra delitos de su institución
    return $query->where('institucion_id', auth()->user()->institucion_id);
    }

  public static function mutateFormDataBeforeCreate(array $data): array
{
    $data['user_id'] = auth()->id();
    $data['institucion_id'] = auth()->user()->institucion_id;
    return $data;
}


    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('Código')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('delincuentes.nombre')
                ->label('Delincuente')
                ->searchable()
                ->formatStateUsing(function ($record) {
                    return static::getDelincuentesString($record, 'nombre_completo');
                }),
            Tables\Columns\TextColumn::make('delincuentes.rut')
                ->label('RUT')
                ->searchable()
                ->formatStateUsing(function ($record) {
                    return static::getDelincuentesString($record, 'rut');
                }),
            Tables\Columns\TextColumn::make('codigoDelito.codigo')
                ->label('Código')
                ->sortable(),
            Tables\Columns\TextColumn::make('descripcion')
                ->label('Descripción')
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    
                    if (strlen($state) <= 50) {
                        return null;
                    }
                    
                    return $state;
                }),
            Tables\Columns\TextColumn::make('ubicacion')
                ->label('Ubicación')
                ->limit(30)
                ->action(
                    Tables\Actions\Action::make('verUbicacionMapa')
                        ->label('Ver ubicación')
                        ->icon('heroicon-o-map-pin')
                        ->modalHeading('📍 Ubicación del Delito')
                        ->modalContent(fn($record) =>
                            $record->ubicacion
                                ? view('filament.custom.simple-location-view', [
                                    'address' => $record->ubicacion,
                                ])
                                : '<div class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-2">📍</div>
                                    <div>No hay ubicación registrada para este delito</div>
                                   </div>'
                        )
                        ->modalWidth('lg')
                        ->visible(fn($record) => !empty($record->ubicacion)),
                ),
            Tables\Columns\TextColumn::make('region.nombre')
                ->label('Región')
                ->sortable(),
            Tables\Columns\TextColumn::make('comuna.nombre')
                ->label('Comuna')
                ->sortable(),
            Tables\Columns\TextColumn::make('sector.nombre')
                ->label('Sector')
                ->sortable(),
            Tables\Columns\TextColumn::make('fecha')
                ->label('Fecha')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de Registro')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Denunciado por')
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('institucion.nombre')
                ->label('Institución')
                ->sortable()
                ->toggleable(),
        ])
        ->filters([
            SelectFilter::make('codigo_delito_id')
                ->label('Código de Delito')
                ->options(function () {
                    // Solo mostrar códigos que tienen delitos asociados
                    return CodigoDelito::whereHas('delitos')
                        ->orderBy('codigo')
                        ->get()
                        ->mapWithKeys(fn($codigo) => [$codigo->id => $codigo->codigo . ' - ' . $codigo->descripcion])
                        ->toArray();
                }),

            SelectFilter::make('region_id')
                ->label('Región')
                ->options(function () {
                    // Solo mostrar regiones que tienen delitos
                    return Region::whereHas('delitos')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                }),

            SelectFilter::make('comuna_id')
                ->label('Comuna')
                ->options(function () {
                    // Solo mostrar comunas que tienen delitos
                    return Comuna::whereHas('delitos')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                }),

            SelectFilter::make('sector_id')
                ->label('Sector')
                ->options(function () {
                    // Solo mostrar sectores que tienen delitos
                    return Sector::whereHas('delitos')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                }),

            SelectFilter::make('delincuente_id')
                ->label('Delincuente')
                ->options(function () {
                    // Solo mostrar delincuentes que tienen delitos
                    return \App\Models\Delincuente::whereHas('delitos')
                        ->orderBy('nombre')
                        ->get()
                        ->mapWithKeys(fn($d) => [$d->id => $d->nombre . ' ' . $d->apellidos . ' (' . $d->rut . ')'])
                        ->toArray();
                })
                ->searchable()
                ->multiple(),

            SelectFilter::make('institucion_id')
                ->label('Institución')
                ->options(function () {
                    // Solo mostrar instituciones que tienen delitos
                    return \App\Models\Institucion::whereHas('delitos')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                })
                ->visible(fn () => auth()->user()->hasRole(['Administrador General', 'Super Admin'])),

            Filter::make('fecha_delito')
                ->form([
                    Forms\Components\DatePicker::make('fecha_desde')
                        ->label('Fecha Desde'),
                    Forms\Components\DatePicker::make('fecha_hasta')
                        ->label('Fecha Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['fecha_desde'],
                            fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                        )
                        ->when(
                            $data['fecha_hasta'],
                            fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                        );
                }),

            Filter::make('fecha_registro')
                ->form([
                    Forms\Components\DatePicker::make('registro_desde')
                        ->label('Registro Desde'),
                    Forms\Components\DatePicker::make('registro_hasta')
                        ->label('Registro Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['registro_desde'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['registro_hasta'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
        ])
        ->headerActions([
            Tables\Actions\Action::make('exportar_pdf')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Obtener datos con relaciones cargadas
                    $query = static::getEloquentQuery()
                        ->with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector', 'user', 'institucion']);
                    
                    $delitos = $query->get();
                    
                    $filtrosAplicados = [
                        'Usuario' => auth()->user()->name,
                        'Rol' => auth()->user()->roles->first()->name ?? 'Sin rol',
                    ];
                    
                    if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        $filtrosAplicados['Institución'] = auth()->user()->institucion->nombre ?? 'N/A';
                    }
                    
                    $data = [
                        'delitos' => $delitos,
                        'titulo' => 'Reporte de Delitos',
                        'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                        'usuario' => auth()->user()->name,
                        'institucion' => auth()->user()->institucion->nombre ?? 'Sistema',
                        'filtros_aplicados' => $filtrosAplicados,
                        'periodo' => 'Todos los registros',
                    ];
                    
                    return response()->streamDownload(function () use ($data) {
                        echo Pdf::loadHtml(
                            Blade::render('reports.delitos-pdf', $data)
                        )->setPaper('A4', 'landscape')->stream();
                    }, 'reporte_delitos_' . now()->format('Y_m_d_H_i_s') . '.pdf');
                })
                ->visible(fn () => auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador'])),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['Administrador General'])),
                BulkAction::make('exportar_seleccionados')
                    ->label('Exportar Seleccionados a PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return static::exportSelectedToPdf($records);
                    })
                    ->visible(fn () => auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador'])),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDelitos::route('/'),
            'create' => Pages\CreateDelito::route('/create'),
            'edit' => Pages\EditDelito::route('/{record}/edit'),
        ];
    }

    /**
     * Helper method para obtener delincuentes de forma segura
     */
    private static function getDelincuentesString($delito, $field = 'nombre_completo')
    {
        try {
            if (!$delito->delincuentes || $delito->delincuentes->count() === 0) {
                return 'N/A';
            }
            
            if ($field === 'nombre_completo') {
                return $delito->delincuentes->map(function ($delincuente) {
                    return trim($delincuente->nombre . ' ' . $delincuente->apellidos);
                })->implode(', ');
            } elseif ($field === 'rut') {
                return $delito->delincuentes->pluck('rut')->implode(', ');
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            \Log::error('Error obteniendo delincuentes: ' . $e->getMessage());
            return 'Error';
        }
    }

    public static function exportToPdfSimple()
    {
        try {
            // Obtener todos los delitos filtrados por permisos del usuario
            $query = static::getEloquentQuery();
            
            $filtrosAplicados = [
                'Usuario' => auth()->user()->name,
                'Rol' => auth()->user()->roles->first()->name ?? 'Sin rol',
            ];
            
            // Si el usuario no es Admin General, agregar filtro de institución
            if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                $filtrosAplicados['Institución'] = auth()->user()->institucion->nombre ?? 'N/A';
            }

            // Usar el servicio para generar el PDF
            return PdfExportService::exportDelitosToPdf($query, $filtrosAplicados, 'Reporte de Delitos');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar PDF')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function exportToPdf($livewire)
    {
        try {
            // Obtener los datos filtrados de la tabla actual
            $query = static::getEloquentQuery();
            
            // Obtener filtros activos usando el método correcto de Filament
            $tableFilters = [];
            $filtrosAplicados = [];
            
            // Obtener los filtros de la tabla usando el método correcto
            if (method_exists($livewire, 'getTable')) {
                $table = $livewire->getTable();
                if (method_exists($table, 'getFilters')) {
                    $filters = $table->getFilters();
                    foreach ($filters as $filterName => $filter) {
                        $filterState = $livewire->getTableFilterState($filterName);
                        if (!empty($filterState)) {
                            $tableFilters[$filterName] = $filterState;
                            $query = static::applyTableFilter($query, $filterName, $filterState);
                            $filtrosAplicados[static::getFilterLabel($filterName)] = static::getFilterValue($filterName, $filterState);
                        }
                    }
                }
            }

            // Usar el servicio para generar el PDF
            return PdfExportService::exportDelitosToPdf($query, $filtrosAplicados, 'Reporte de Delitos');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar PDF')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /*
    // MÉTODO 2 ALTERNATIVO - Para referencia (no implementado)
    Tables\Actions\Action::make('exportar_pdf_metodo2')
        ->action(function () {
            $delitos = static::getEloquentQuery()
                ->with(['delincuentes', 'codigoDelito', 'region', 'comuna', 'sector'])
                ->get();
            
            $pdf = Pdf::loadView('reports.delitos-pdf', [
                'delitos' => $delitos,
                'titulo' => 'Reporte de Delitos',
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            ])->setPaper('A4', 'landscape');
            
            return response()->streamDownload(fn () => print($pdf->output()), 'delitos.pdf');
        })
    */

    public static function exportSelectedToPdf(Collection $records)
    {
        try {
            $filtrosAplicados = [
                'Usuario' => auth()->user()->name,
                'Rol' => auth()->user()->roles->first()->name ?? 'Sin rol',
                'Selección' => $records->count() . ' registros seleccionados',
            ];
            
            if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                $filtrosAplicados['Institución'] = auth()->user()->institucion->nombre ?? 'N/A';
            }
            
            $data = [
                'delitos' => $records,
                'titulo' => 'Reporte de Delitos Seleccionados',
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name,
                'institucion' => auth()->user()->institucion->nombre ?? 'Sistema',
                'filtros_aplicados' => $filtrosAplicados,
                'periodo' => 'Registros seleccionados',
            ];
            
            return response()->streamDownload(function () use ($data) {
                echo Pdf::loadHtml(
                    Blade::render('reports.delitos-pdf', $data)
                )->setPaper('A4', 'landscape')->stream();
            }, 'delitos_seleccionados_' . now()->format('Y_m_d_H_i_s') . '.pdf');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar PDF')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private static function applyTableFilter(Builder $query, string $filterName, $filterState): Builder
    {
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
        
        return $query;
    }

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

        return $labels[$filterName] ?? $filterName;
    }

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
                        return $filterState['value'];
                }
            } elseif (isset($filterState['values'])) {
                // Para SelectFilter múltiple
                return count($filterState['values']) . ' seleccionados';
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
