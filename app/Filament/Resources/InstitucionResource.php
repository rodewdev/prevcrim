<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitucionResource\Pages;
use App\Models\Institucion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstitucionResource extends Resource
{
    protected static ?string $model = Institucion::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->unique(Institucion::class, 'nombre')
                ->maxLength(255),
            Forms\Components\Textarea::make('descripcion')
                ->maxLength(500)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nombre')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('descripcion')
                ->limit(50)
                ->wrap(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitucions::route('/'),
            'create' => Pages\CreateInstitucion::route('/create'),
            'edit' => Pages\EditInstitucion::route('/{record}/edit'),
        ];
    }
}
