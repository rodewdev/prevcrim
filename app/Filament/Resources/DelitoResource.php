<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelitoResource\Pages;
use App\Models\Delito;
use App\Models\Sector;
use App\Models\CodigoDelito;
use App\Models\Region;
use App\Models\Comuna;
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

class DelitoResource extends Resource
{
    protected static ?string $model = Delito::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

public static function canViewAny(): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Jefe de zona', 'Operador']);
    
}

public static function canCreate(): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Operador']);
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->hasRole(['Administrador General', 'Operador']);
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->hasRole(['Administrador General']);
}
    public static function form(Form $form): Form
    {
        return $form->schema([
            Hidden::make('delincuente_id'),
            Forms\Components\Select::make('delincuente_id')
                ->label('Delincuente (RUT)')
                ->options(function () {
                    return \App\Models\Delincuente::all()->mapWithKeys(function ($delincuente) {
                        return [$delincuente->id => $delincuente->rut . ' - ' . $delincuente->nombre];
                    });
                })
                ->searchable()
                ->required(),
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
                ->maxLength(1000),
            Forms\Components\Select::make('region_id')
                ->label('Región')
                ->options(Region::pluck('nombre', 'id'))
                ->required()
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
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('sector_id')
                ->label('Sector')
                ->relationship('sector', 'nombre')
                ->required(),
            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha del Delito')
                ->required(),
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
                ->searchable(),
            Tables\Columns\TextColumn::make('codigoDelito.codigo')
                ->label('Código')
                ->sortable(),
            Tables\Columns\TextColumn::make('descripcion')
                ->label('Descripción')
                ->limit(50),
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
                ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
    ->label('Denunciado por')
    ->sortable(),
Tables\Columns\TextColumn::make('institucion.nombre')
    ->label('Institución')
    ->sortable(),
        ])
            ->filters([
            // Filtro de delincuentes ordenados alfabéticamente y selección múltiple (usando Filter::make)
            Filter::make('delincuente')
                ->label('Delincuente (alfabético)')
                ->form([
                    Forms\Components\Select::make('delincuente_id')
                        ->label('Delincuente')
                        ->options(\App\Models\Delincuente::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->preload()
                        ->multiple(),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['delincuente_id'])) {
                        $ids = is_array($data['delincuente_id']) ? $data['delincuente_id'] : [$data['delincuente_id']];
                        $query->whereHas('delincuentes', function ($q) use ($ids) {
                            $q->whereIn('delincuentes.id', $ids);
                        });
                    }
                }),

            // Filtro por delito cometido (agrupado por código y descripción)
            SelectFilter::make('codigo_delito_id')
                ->label('Delito cometido')
                ->options(function () {
                    return CodigoDelito::orderBy('codigo')->get()->mapWithKeys(function ($cd) {
                        return [$cd->id => $cd->codigo . ' - ' . $cd->descripcion];
                    });
                })
                ->searchable()
                ->preload(),

            // Filtro por comuna de residencia del delincuente (usando Filter::make)
            Filter::make('delincuente_comuna')
                ->label('Comuna de residencia del delincuente')
                ->form([
                    Forms\Components\Select::make('comuna_id')
                        ->label('Comuna de residencia')
                        ->options(\App\Models\Comuna::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['comuna_id'])) {
                        $query->whereHas('delincuentes', function ($q) use ($data) {
                            $q->where('comuna_id', $data['comuna_id']);
                        });
                    }
                }),

            // Filtro por comuna donde se vio por última vez al delincuente (usando Filter::make)
            Filter::make('ultimo_lugar_visto')
                ->label('Comuna donde se vio por última vez al delincuente')
                ->form([
                    Forms\Components\Select::make('comuna_id')
                        ->label('Comuna último avistamiento')
                        ->options(\App\Models\Comuna::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['comuna_id'])) {
                        $query->whereHas('delincuentes', function ($q) use ($data) {
                            $q->where('ultimo_lugar_visto', 'like', "%{$data['comuna_id']}%");
                        });
                    }
                }),

            // Filtro por parentesco (delincuentes con familiares)
            Filter::make('tiene_familiares')
                ->label('Con familiares registrados')
                ->query(fn (Builder $query, $value) => $value ? $query->whereHas('delincuentes.familiares') : $query),

            // Filtro por comuna del delito
            SelectFilter::make('comuna_id')
                ->label('Comuna del delito')
                ->relationship('comuna', 'nombre')
                ->searchable()
                ->preload(),

            // Filtro por sector
            SelectFilter::make('sector_id')
                ->label('Sector')
                ->relationship('sector', 'nombre')
                ->searchable()
                ->preload(),

            // Filtro por rango de fechas
            Filter::make('fecha')
                ->form([
                    Forms\Components\DatePicker::make('desde')->label('Desde'),
                    Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['desde'], fn (Builder $q, $date) => $q->whereDate('fecha', '>=', $date))
                        ->when($data['hasta'], fn (Builder $q, $date) => $q->whereDate('fecha', '<=', $date));
                }),

            // Ranking de comunas con más delitos
            Filter::make('ranking_comunas')
                ->label('Ranking comunas con más delitos')
                ->query(function (Builder $query) {
                    $query->selectRaw('comuna_id, count(*) as total')
                        ->groupBy('comuna_id')
                        ->orderByDesc('total');
                }),

            // Ranking de sectores con más delitos
            Filter::make('ranking_sectores')
                ->label('Ranking sectores con más delitos')
                ->query(function (Builder $query) {
                    $query->selectRaw('sector_id, count(*) as total')
                        ->groupBy('sector_id')
                        ->orderByDesc('total');
                }),
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
            'index' => Pages\ListDelitos::route('/'),
            'create' => Pages\CreateDelito::route('/create'),
            'edit' => Pages\EditDelito::route('/{record}/edit'),
        ];
    }
}
