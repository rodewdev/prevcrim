<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrullajeAsignacionResource\Pages;
use App\Filament\Resources\PatrullajeAsignacionResource\RelationManagers;
use App\Models\PatrullajeAsignacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatrullajeAsignacionResource extends Resource
{
    protected static ?string $model = PatrullajeAsignacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('comuna_id')
                ->label('Comuna')
                ->relationship('comuna', 'nombre')
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($state, $set) => $set('sector_id', null)),

            Forms\Components\Select::make('sector_id')
                ->label('Sector')
                ->options(function ($get) {
                    $comunaId = $get('comuna_id');
                    if (!$comunaId) return [];
                    return \App\Models\Comuna::find($comunaId)?->sectores()->pluck('nombre', 'id') ?? [];
                })
                ->required(),

            Forms\Components\Select::make('prioridad')
                ->label('Prioridad')
                ->options([1 => 'Alta', 2 => 'Media', 3 => 'Baja'])
                ->required(),

            Forms\Components\DatePicker::make('fecha_inicio')
                ->label('Fecha de inicio')
                ->required(),

            Forms\Components\DatePicker::make('fecha_fin')
                ->label('Fecha de fin')
                ->nullable(),

            Forms\Components\Textarea::make('observaciones')
                ->label('Observaciones')
                ->nullable(),

            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),

            Forms\Components\Hidden::make('institucion_id')
                ->default(fn() => auth()->user()->institucion_id),

            Forms\Components\Hidden::make('user_id')
                ->default(fn() => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListPatrullajeAsignacions::route('/'),
            'create' => Pages\CreatePatrullajeAsignacion::route('/create'),
            'edit' => Pages\EditPatrullajeAsignacion::route('/{record}/edit'),
        ];
    }
}
