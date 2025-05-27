<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RolResource\Pages;
use App\Filament\Resources\RolResource\RelationManagers;
use App\Models\Rol;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Database\Eloquent\Model;
class RolResource extends Resource
{
    protected static ?string $model = Rol::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
{
    // Solo el Administrador General puede acceder a los roles
    return auth()->user()->hasRole('Administrador General');
}

public static function canCreate(): bool
{
    // Solo el Administrador General puede crear roles
    return auth()->user()->hasRole('Administrador General');
}

public static function canEdit(Model $record): bool
{
    // Solo el Administrador General puede editar roles
    return auth()->user()->hasRole('Administrador General');
}

public static function canDelete(Model $record): bool
{
    // Solo el Administrador General puede eliminar roles
    return auth()->user()->hasRole('Administrador General');
} 
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(Rol::class, 'name')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListRols::route('/'),
            'create' => Pages\CreateRol::route('/create'),
            'edit' => Pages\EditRol::route('/{record}/edit'),
        ];
    }
}
