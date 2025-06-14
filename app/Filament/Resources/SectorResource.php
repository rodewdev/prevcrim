<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectorResource\Pages;
use App\Filament\Resources\SectorResource\RelationManagers;
use App\Models\Sector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectorResource extends Resource
{
    protected static ?string $model = Sector::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                /*Forms\Components\TextInput::make('institucion_id')
                    ->required()
                    ->numeric(), */
            Forms\Components\Select::make('comuna_id')
                ->label('Comuna')
                ->relationship(
                    name: 'comuna', 
                    titleAttribute: 'nombre',
                    modifyQueryUsing: fn (Builder $query) => $query
                )
                ->searchable()
                ->preload() // Precargar opciones
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),/*
                Tables\Columns\TextColumn::make('institucion.nombre')
                    ->label('Institución')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('comuna_id')
                    ->numeric()
                    ->sortable(),*/
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectors::route('/'),
            'create' => Pages\CreateSector::route('/create'),
            'edit' => Pages\EditSector::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }
    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
