<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelincuenteResource\Pages;
use App\Models\Delincuente;
use App\Models\Comuna;
use App\Models\Region;
use App\Models\Sector;
use App\Models\CodigoDelito;
use App\Rules\RutChileno;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DelincuenteResource extends Resource
{
    protected static ?string $model = Delincuente::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Operador', 'Jefe de Zona']);
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
            Forms\Components\TextInput::make('rut')
                ->label('RUT')
                ->required()
                ->unique(Delincuente::class, 'rut', ignoreRecord: true)
                ->disabled(fn ($context) => $context === 'edit') // Solo deshabilitado en edición
                ->maxLength(12)
                ->rules([new RutChileno()])
                ->validationMessages([
                    'required' => 'Campo requerido',
                    'unique' => 'Este RUT ya está registrado',
                ])
                ->live()
                ->afterStateUpdated(function (string $state, Forms\Components\TextInput $component, $set, $livewire) {
                    $cleanRut = RutChileno::clean($state);

                    if (strlen($cleanRut) > 0) {
                        if (strlen($cleanRut) < 7) {
                            $livewire->addError('rut', 'El RUT debe tener al menos 7 dígitos más el dígito verificador.');
                            return;
                        }

                        $numero = substr($cleanRut, 0, -1);
                        $dv = substr($cleanRut, -1);

                        if (strlen($cleanRut) >= 8 && strlen($cleanRut) <= 9 && $dv === RutChileno::calcularDv($numero)) {
                            $formattedRut = RutChileno::formatRut($cleanRut);
                            $set('rut', $formattedRut);
                            $livewire->resetValidation('rut');
                        } else {
                            $livewire->addError('rut', 'El RUT ingresado no es válido.');
                        }
                    } else {
                        $livewire->resetValidation('rut');
                    }
                }),
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255)
                ->rule('regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/')
                ->validationMessages([
                    'required' => 'Campo requerido',
                    'regex' => 'El nombre solo puede contener letras, tildes y espacios.',
                ]),
            Forms\Components\TextInput::make('apellidos')
                ->label('Apellidos')
                ->required()
                ->maxLength(255)
                ->rule('regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/')
                ->validationMessages([
                    'required' => 'Campo requerido',
                    'regex' => 'Los apellidos solo pueden contener letras, tildes y espacios.',
                ]),
            Forms\Components\TextInput::make('alias')
                ->label('Alias')
                ->maxLength(100)
                ->rule('regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]*$/')
                ->validationMessages([
                    'regex' => 'El alias solo puede contener letras, tildes y espacios.',
                ]),
            Forms\Components\TextInput::make('domicilio')
                ->label('Domicilio')
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->live()
                ->readonly(),
            Forms\Components\ViewField::make('domicilio_mapa')
                ->view('filament.custom.address-map-field', [
                    'id' => 'domicilio',
                    'label' => 'Domicilio (mapa)',
                    'addressField' => 'domicilio',
                ]),
            Forms\Components\Select::make('comuna_id')
                ->label('Comuna de Residencia')
                ->relationship('comuna', 'nombre')
                ->searchable()
                ->preload()
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->afterStateUpdated(fn (callable $set) => $set('region_display', null)),
            Forms\Components\TextInput::make('ultimo_lugar_visto')
                ->label('Último lugar visto')
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Campo requerido',
                ])
                ->live()
                ->readonly(),
            Forms\Components\ViewField::make('ultimo_lugar_visto_mapa')
                ->view('filament.custom.address-map-field', [
                    'id' => 'ultimo_lugar_visto',
                    'label' => 'Último lugar visto (mapa)',
                    'addressField' => 'ultimo_lugar_visto',
                ]),
            Forms\Components\TextInput::make('telefono_fijo')
                ->label('Teléfono fijo')
                ->maxLength(20)
                ->rule('regex:/^[0-9\+\-\(\)\s]*$/')
                ->validationMessages([
                    'regex' => 'El teléfono solo puede contener números, espacios y caracteres (+, -, (, )).',
                ]),
            Forms\Components\TextInput::make('celular')
                ->label('Celular')
                ->maxLength(20)
                ->rule('regex:/^[0-9\+\-\(\)\s]*$/')
                ->validationMessages([
                    'regex' => 'El celular solo puede contener números, espacios y caracteres (+, -, (, )).',
                ]),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255),
            Forms\Components\DatePicker::make('fecha_nacimiento')
                ->label('Fecha de nacimiento'),
            Forms\Components\Select::make('estado')
                ->label('Estado')
                ->options([
                    'P' => 'Preso',
                    'L' => 'Libre',
                    'A' => 'Orden de Arresto',
                ])
                ->required()
                ->validationMessages([
                    'required' => 'Campo requerido',
                ]),
            Forms\Components\FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->disk('public')
                ->directory('delincuentes')
                ->visibility('public')
                ->nullable(),
            Forms\Components\Repeater::make('familiares')
                ->label('Familiares')
                ->relationship('familiares')
                ->saveRelationshipsUsing(function ($state, $record) {
                    // Elimina relaciones actuales
                    $record->familiares()->detach();
                    // Vuelve a asociar solo los IDs seleccionados y sus parentescos
                    foreach ($state as $item) {
                        if (!empty($item['familiar_id']) && !empty($item['parentesco'])) {
                            $record->familiares()->attach($item['familiar_id'], ['parentesco' => $item['parentesco']]);
                        }
                    }
                })
                ->schema([
                    Forms\Components\Select::make('familiar_id')
                        ->label('Familiar')
                        ->options(function ($get, $livewire) {
                            $delincuenteId = $livewire->record->id ?? null;
                            return \App\Models\Delincuente::query()
                                ->when($delincuenteId, fn($q) => $q->where('id', '!=', $delincuenteId))
                                ->get()
                                ->mapWithKeys(fn($d) => [$d->id => $d->nombre . ' ' . $d->apellidos . ', ' . $d->rut]);
                        })
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search, $get, $livewire) {
                            $delincuenteId = $livewire->record->id ?? null;
                            return \App\Models\Delincuente::query()
                                ->where(function($q) use ($search) {
                                    $q->where('rut', 'like', "%$search%")
                                      ->orWhere('nombre', 'like', "%$search%")
                                      ->orWhere('apellidos', 'like', "%$search%") ;
                                })
                                ->when($delincuenteId, fn($q) => $q->where('id', '!=', $delincuenteId))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn($d) => [$d->id => $d->nombre . ' ' . $d->apellidos . ', ' . $d->rut]);
                        })
                        ->required()
                        ->validationMessages([
                            'required' => 'Campo requerido',
                        ]),
                    Forms\Components\Select::make('parentesco')
                        ->label('Parentesco')
                        ->options([
                            'padre' => 'Padre',
                            'madre' => 'Madre',
                            'hijo' => 'Hijo',
                            'hija' => 'Hija',
                            'abuelo' => 'Abuelo',
                            'abuela' => 'Abuela',
                            'bisabuelo' => 'Bisabuelo',
                            'bisabuela' => 'Bisabuela',
                            'tatarabuelo' => 'Tatarabuelo',
                            'tatarabuela' => 'Tatarabuela',
                            'nieto' => 'Nieto',
                            'nieta' => 'Nieta',
                            'bisnieto' => 'Bisnieto',
                            'bisnieta' => 'Bisnieta',
                            'tataranieto' => 'Tataranieto',
                            'tataranieta' => 'Tataranieta',
                            'hermano' => 'Hermano',
                            'hermana' => 'Hermana',
                            'medio hermano' => 'Medio hermano',
                            'media hermana' => 'Media hermana',
                            'primo' => 'Primo',
                            'prima' => 'Prima',
                            'tio' => 'Tío',
                            'tia' => 'Tía',
                            'sobrino' => 'Sobrino',
                            'sobrina' => 'Sobrina',
                            'esposo' => 'Esposo',
                            'esposa' => 'Esposa',
                            'conyuge' => 'Cónyuge',
                            'suegro' => 'Suegro',
                            'suegra' => 'Suegra',
                            'yerno' => 'Yerno',
                            'nuera' => 'Nuera',
                            'cuñado' => 'Cuñado',
                            'cuñada' => 'Cuñada',
                            'tio abuelo' => 'Tío abuelo',
                            'tia abuela' => 'Tía abuela',
                            'primo segundo' => 'Primo segundo',
                            'prima segunda' => 'Prima segunda',
                            'primo tercero' => 'Primo tercero',
                            'prima tercera' => 'Prima tercera',
                        ])
                        ->required()
                        ->validationMessages([
                            'required' => 'Campo requerido',
                        ])
                        ->afterStateUpdated(function ($state, $component, $set, $get, $livewire) {
                            $parentesco = strtolower($state);
                            $familiarId = $get('familiar_id');
                            $repeaterState = $component->getContainer()->getParentComponent()->getState();
                            $inversos = [
                                'padre' => 'hijo',
                                'madre' => 'hijo',
                                'hijo' => 'padre',
                                'hija' => 'padre',
                                'abuelo' => 'nieto',
                                'abuela' => 'nieto',
                                'bisabuelo' => 'bisnieto',
                                'bisabuela' => 'bisnieto',
                                'tatarabuelo' => 'tataranieto',
                                'tatarabuela' => 'tataranieto',
                                'nieto' => 'abuelo',
                                'nieta' => 'abuelo',
                                'bisnieto' => 'bisabuelo',
                                'bisnieta' => 'bisabuelo',
                                'tataranieto' => 'tatarabuelo',
                                'tataranieta' => 'tatarabuelo',
                                'hermano' => 'hermano',
                                'hermana' => 'hermana',
                                'medio hermano' => 'medio hermano',
                                'media hermana' => 'media hermana',
                                'primo' => 'primo',
                                'prima' => 'prima',
                                'tio' => 'sobrino',
                                'tia' => 'sobrino',
                                'sobrino' => 'tio',
                                'sobrina' => 'tio',
                                'esposo' => 'esposa',
                                'esposa' => 'esposo',
                                'conyuge' => 'conyuge',
                                'suegro' => 'yerno',
                                'suegra' => 'yerno',
                                'yerno' => 'suegro',
                                'nuera' => 'suegro',
                                'cuñado' => 'cuñado',
                                'cuñada' => 'cuñada',
                                'tio abuelo' => 'sobrino nieto',
                                'tia abuela' => 'sobrino nieto',
                                'primo segundo' => 'primo segundo',
                                'prima segunda' => 'prima segunda',
                                'primo tercero' => 'primo tercero',
                                'prima tercera' => 'prima tercera',
                            ];
                            // Eliminar cualquier relación inversa previa en el estado del repeater
                            $delincuenteId = $livewire->record->id ?? null;
                            $nuevo = collect($repeaterState)
                                ->reject(function($item) use ($delincuenteId, $inversos, $parentesco) {
                                    return isset($item['familiar_id'], $item['parentesco']) &&
                                        $item['familiar_id'] == $delincuenteId &&
                                        strtolower($item['parentesco']) == $inversos[$parentesco];
                                })
                                ->values()
                                ->toArray();
                            // Agregar la relación inversa solo si no existe
                            if ($familiarId && isset($inversos[$parentesco])) {
                                $yaExiste = false;
                                foreach ($nuevo as $item) {
                                    if (
                                        isset($item['familiar_id'], $item['parentesco']) &&
                                        $item['familiar_id'] == $delincuenteId &&
                                        strtolower($item['parentesco']) == $inversos[$parentesco]
                                    ) {
                                        $yaExiste = true;
                                        break;
                                    }
                                }
                                if (!$yaExiste) {
                                    $nuevo[] = [
                                        'familiar_id' => $delincuenteId,
                                        'parentesco' => $inversos[$parentesco],
                                    ];
                                }
                            }
                            $set('familiares', $nuevo);
                        }),
                ])
                ->columns(2)
                ->createItemButtonLabel('Agregar familiar'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rut')
                    ->label('RUT')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias'),
                Tables\Columns\TextColumn::make('comuna.nombre')
                    ->label('Comuna')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('domicilio')
                    ->label('Domicilio')
                    ->limit(30)
                    ->action(
                        Tables\Actions\Action::make('verDomicilioMapa')
                            ->label('Ver domicilio')
                            ->icon('heroicon-o-home')
                            ->modalHeading('🏠 Domicilio del Delincuente')
                            ->modalContent(fn($record) =>
                                $record->domicilio
                                    ? view('filament.custom.simple-location-view', [
                                        'address' => $record->domicilio,
                                    ])
                                    : '<div class="text-center py-8 text-gray-500">
                                        <div class="text-4xl mb-2">🏠</div>
                                        <div>No hay domicilio registrado</div>
                                       </div>'
                            )
                            ->modalWidth('lg')
                            ->visible(fn($record) => !empty($record->domicilio)),
                    ),
                Tables\Columns\TextColumn::make('ultimo_lugar_visto')
                    ->label('Último lugar visto')
                    ->limit(30)
                    ->action(
                        Tables\Actions\Action::make('verUltimoLugarMapa')
                            ->label('Ver ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->modalHeading('👁️ Último Lugar Visto')
                            ->modalContent(fn($record) =>
                                $record->ultimo_lugar_visto
                                    ? view('filament.custom.simple-location-view', [
                                        'address' => $record->ultimo_lugar_visto,
                                    ])
                                    : '<div class="text-center py-8 text-gray-500">
                                        <div class="text-4xl mb-2">👁️</div>
                                        <div>No hay ubicación registrada</div>
                                       </div>'
                            )
                            ->modalWidth('lg')
                            ->visible(fn($record) => !empty($record->ultimo_lugar_visto)),
                    ),
                Tables\Columns\TextColumn::make('telefono_fijo')
                    ->label('Teléfono fijo'),
                Tables\Columns\TextColumn::make('celular')
                    ->label('Celular'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->label('Fecha de nacimiento')
                    ->date(),
                Tables\Columns\TextColumn::make('familiares')
                    ->label('Familiares')
                    ->formatStateUsing(fn($record) =>
                        $record->familiares->isNotEmpty()
                            ? $record->familiares->count() . ' familiar' . ($record->familiares->count() > 1 ? 'es' : '')
                            : 'No registra')
                    ->toggleable()
                    ->wrap()
                    ->limit(30)
                    ->extraAttributes(['style' => 'max-width:120px; white-space:normal; word-break:break-word;'])
                    ->action(
                        Tables\Actions\Action::make('verFamiliares')
                            ->label('Ver familiares')
                            ->icon('heroicon-o-eye')
                            ->modalHeading('Familiares del delincuente')
                            ->modalSubheading(fn($record) => $record->nombre . ' ' . $record->apellidos . ' (' . $record->rut . ')')
                            ->modalContent(fn($record) =>
                                $record->familiares->isNotEmpty()
                                    ? view('filament.custom.familiares-list', ['familiares' => $record->familiares])
                                    : 'No registra'
                            )
                            ->visible(fn($record) => $record->familiares->isNotEmpty())
                    ),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'danger' => 'P',
                        'warning' => 'A',
                        'success' => 'L',
                    ])
                    ->sortable(),
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->visibility('public')
                    ->size(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(function () {
                        // Solo mostrar estados que están en uso
                        $estados = Delincuente::whereNotNull('estado')
                            ->distinct()
                            ->pluck('estado')
                            ->filter()
                            ->mapWithKeys(function ($estado) {
                                $labels = [
                                    'P' => 'Preso',
                                    'L' => 'Libre', 
                                    'A' => 'Orden de Arresto'
                                ];
                                return [$estado => $labels[$estado] ?? $estado];
                            });
                        return $estados->toArray();
                    }),
                SelectFilter::make('comuna_id')
                    ->label('Comuna de Residencia')
                    ->options(function () {
                        // Solo mostrar comunas que tienen delincuentes
                        return Comuna::whereHas('delincuentes')
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id')
                            ->toArray();
                    }),
                SelectFilter::make('codigo_delito')
                    ->label('Código de Delito')
                    ->options(function () {
                        // Solo mostrar códigos de delito que tienen delincuentes asociados
                        return CodigoDelito::whereHas('delitos', function ($query) {
                            $query->whereHas('delincuentes');
                        })
                        ->orderBy('codigo')
                        ->get()
                        ->mapWithKeys(fn($codigo) => [$codigo->id => $codigo->codigo . ' - ' . $codigo->descripcion])
                        ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('delitos.codigoDelito', function ($q) use ($value) {
                                $q->where('id', $value);
                            })
                        );
                    }),
                SelectFilter::make('region')
                    ->label('Región')
                    ->options(function () {
                        // Solo mostrar regiones que tienen delincuentes (a través de delitos)
                        return Region::whereHas('delitos', function ($query) {
                            $query->whereHas('delincuentes');
                        })
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('delitos.region', function ($q) use ($value) {
                                $q->where('id', $value);
                            })
                        );
                    }),
                SelectFilter::make('comuna')
                    ->label('Comuna (Delitos)')
                    ->options(function () {
                        // Solo mostrar comunas que tienen delitos con delincuentes
                        return Comuna::whereHas('delitos', function ($query) {
                            $query->whereHas('delincuentes');
                        })
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('delitos.comuna', function ($q) use ($value) {
                                $q->where('id', $value);
                            })
                        );
                    }),
                SelectFilter::make('sector')
                    ->label('Sector')
                    ->options(function () {
                        // Solo mostrar sectores que tienen delitos con delincuentes
                        return Sector::whereHas('delitos', function ($query) {
                            $query->whereHas('delincuentes');
                        })
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('delitos.sector', function ($q) use ($value) {
                                $q->where('id', $value);
                            })
                        );
                    }),
                Tables\Filters\Filter::make('fecha_comision')
                    ->label('Fecha de Comisión de Delitos')
                    ->form([
                        DatePicker::make('fecha_desde')
                            ->label('Desde'),
                        DatePicker::make('fecha_hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->whereHas('delitos', function ($query) use ($date) {
                                    $query->whereDate('fecha', '>=', $date);
                                })
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereHas('delitos', function ($query) use ($date) {
                                    $query->whereDate('fecha', '<=', $date);
                                })
                            );
                    })
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar a PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $query = static::getEloquentQuery();
                        $filtros = [];
                        return \App\Services\PdfExportService::exportDelincuentesToPdf($query, $filtros, 'Reporte de Delincuentes');
                    })
                    ->visible(fn () => auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador'])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDelincuentes::route('/'),
            'create' => Pages\CreateDelincuente::route('/create'),
            'edit' => Pages\EditDelincuente::route('/{record}/edit'),
        ];
    }

    public static function afterSave(Form $form, Model $record): void
    {
        $inversos = [
            'padre' => 'hijo', 'madre' => 'hijo', 'hijo' => 'padre', 'hija' => 'padre',
            'abuelo' => 'nieto', 'abuela' => 'nieto', 'bisabuelo' => 'bisnieto', 'bisabuela' => 'bisnieto',
            'tatarabuelo' => 'tataranieto', 'tatarabuela' => 'tataranieto', 'nieto' => 'abuelo', 'nieta' => 'abuelo',
            'bisnieto' => 'bisabuelo', 'bisnieta' => 'bisabuelo', 'tataranieto' => 'tatarabuelo', 'tataranieta' => 'tatarabuelo',
            'hermano' => 'hermano', 'hermana' => 'hermana', 'medio hermano' => 'medio hermano', 'media hermana' => 'media hermana',
            'primo' => 'primo', 'prima' => 'prima', 'tio' => 'sobrino', 'tia' => 'sobrino', 'sobrino' => 'tio', 'sobrina' => 'tio',
            'esposo' => 'esposa', 'esposa' => 'esposo', 'conyuge' => 'conyuge', 'suegro' => 'yerno', 'suegra' => 'yerno',
            'yerno' => 'suegro', 'nuera' => 'suegro', 'cuñado' => 'cuñado', 'cuñada' => 'cuñada',
            'tio abuelo' => 'sobrino nieto', 'tia abuela' => 'sobrino nieto', 'primo segundo' => 'primo segundo',
            'prima segunda' => 'prima segunda', 'primo tercero' => 'primo tercero', 'prima tercera' => 'prima tercera',
        ];

        // 1. Eliminar TODAS las relaciones inversas de este delincuente en otros delincuentes
        foreach ($inversos as $par => $inv) {
            // Buscar delincuentes que tengan a este como familiar con ese parentesco inverso
            $relaciones = \App\Models\Delincuente::whereHas('familiares', function($q) use ($record, $inv) {
                $q->where('familiar_id', $record->id)->where('parentesco', $inv);
            })->get();
            foreach ($relaciones as $del) {
                $del->familiares()->detach($record->id);
            }
        }

        // 2. Agregar solo las relaciones inversas correctas según el formulario actual
        $familiares = $record->familiares()->withPivot('parentesco')->get();
        foreach ($familiares as $familiar) {
            $parentesco = strtolower($familiar->pivot->parentesco);
            if (isset($inversos[$parentesco])) {
                $inverso = $inversos[$parentesco];
                // Eliminar cualquier relación inversa previa (por seguridad)
                $familiar->familiares()->detach($record->id);
                // Agregar la relación inversa correcta
                $familiar->familiares()->attach($record->id, ['parentesco' => $inverso]);
            }
        }
    }
}
