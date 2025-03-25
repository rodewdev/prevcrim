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
use Illuminate\Database\Eloquent\Builder;

class DelitoResource extends Resource
{
    protected static ?string $model = Delito::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo')
                ->label('Código')
                ->required()
                ->unique(Delito::class, 'codigo')
                ->maxLength(20),
            Forms\Components\Select::make('codigo_delito_id')
                ->label('Código de Delito')
                ->options(CodigoDelito::pluck('descripcion', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Textarea::make('descripcion')
                ->label('Descripción')
                ->required()
                ->maxLength(1000),
            Forms\Components\Select::make('sector_id')
                ->label('Sector')
                ->relationship('sector', 'nombre')
                ->required(),
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
            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha del Delito')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('codigo')
                ->label('Código')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('codigoDelito.codigo')
                ->label('Código')
                ->sortable(),
            Tables\Columns\TextColumn::make('descripcion')
                ->label('Descripción')
                ->limit(50),
            Tables\Columns\TextColumn::make('sector.nombre')
                ->label('Sector')
                ->sortable(),
            Tables\Columns\TextColumn::make('region.nombre')
                ->label('Región')
                ->sortable(),
            Tables\Columns\TextColumn::make('comuna.nombre')
                ->label('Comuna')
                ->sortable(),
            Tables\Columns\TextColumn::make('fecha')
                ->label('Fecha')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de Registro')
                ->dateTime()
                ->sortable(),
        ])
            ->filters([
                SelectFilter::make('sector_id')
                    ->label('Sector')
                    ->relationship('sector', 'nombre'),
                SelectFilter::make('comuna_id')
                    ->label('Comuna')
                    ->relationship('comuna', 'nombre'),
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
