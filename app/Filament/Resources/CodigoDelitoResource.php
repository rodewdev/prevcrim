<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CodigoDelitoResource\Pages;
use App\Filament\Resources\CodigoDelitoResource\RelationManagers;
use App\Models\CodigoDelito;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
class CodigoDelitoResource extends Resource
{
    protected static ?string $model = CodigoDelito::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Códigos de Delitos';
public static function canViewAny(): bool
{
    // Admin tiene acceso total (nombre correcto del rol)
    if (auth()->user()->hasRole('Administrador General')) {
        return true;
    }
    
    // Otros roles no tienen acceso
    return false;
}

public static function canCreate(): bool
{
    // Solo admin (nombre correcto)
    return auth()->user()->hasRole('Administrador General');
}

public static function canEdit(Model $record): bool
{
    // Solo admin (nombre correcto)
    return auth()->user()->hasRole('Administrador General');
}

public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
{
    // Solo admin (nombre correcto)
    return auth()->user()->hasRole('Administrador General');
}
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('descripcion')
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
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
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCodigoDelitos::route('/'),
            'create' => Pages\CreateCodigoDelito::route('/create'),
            'edit' => Pages\EditCodigoDelito::route('/{record}/edit'),
        ];
    }
}
