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


class DelitoResource extends Resource
{
    protected static ?string $model = Delito::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

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
                SelectFilter::make('codigo_delito_id')
                    ->label('Código de Delito')
                    ->relationship('codigoDelito', 'codigo')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('codigo_delito_descripcion')
                    ->label('Descripción del Delito')
                    ->relationship('codigoDelito', 'descripcion')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('region_id')
                    ->label('Región')
                    ->relationship('region', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('comuna_id')
                    ->label('Comuna')
                    ->relationship('comuna', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('sector_id')
                    ->label('Sector')
                    ->relationship('sector', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('delincuente_id')
                    ->label('Delincuente')
                    ->relationship('delincuentes', 'nombre')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('fecha_comision')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde'),
                        Forms\Components\DatePicker::make('fecha_hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_comision', '>=', $date),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_comision', '<=', $date),
                            );
                    })
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
