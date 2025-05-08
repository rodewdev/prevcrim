<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelincuenteResource\Pages;
use App\Models\Delincuente;
use App\Rules\RutChileno;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class DelincuenteResource extends Resource
{
    protected static ?string $model = Delincuente::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('rut')
                ->label('RUT')
                ->required()
                ->unique(Delincuente::class, 'rut')
                ->maxLength(12)
                ->rules([new RutChileno()])
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
                ->maxLength(255),
            Forms\Components\TextInput::make('alias')
                ->label('Alias')
                ->maxLength(100),
            Forms\Components\TextInput::make('domicilio')
                ->label('Domicilio')
                ->maxLength(255),
            Forms\Components\Select::make('estado')
                ->label('Estado')
                ->options([
                    'P' => 'Preso',
                    'L' => 'Libre',
                    'A' => 'Orden de Arresto',
                ])
                ->required(),
            Forms\Components\FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->directory('delincuentes')
                ->nullable(),
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
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias'),
                Tables\Columns\TextColumn::make('domicilio')
                    ->label('Domicilio')
                    ->limit(50),
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
                    ->size(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'P' => 'Preso',
                        'L' => 'Libre',
                        'A' => 'Orden de Arresto',
                    ]),
                SelectFilter::make('codigo_delito')
                    ->relationship('delitos.codigoDelito', 'codigo')
                    ->label('Código de Delito'),
                SelectFilter::make('region')
                    ->relationship('delitos.region', 'nombre')
                    ->label('Región'),
                SelectFilter::make('comuna')
                    ->relationship('delitos.comuna', 'nombre')
                    ->label('Comuna'),
                SelectFilter::make('sector')
                    ->relationship('delitos.sector', 'nombre')
                    ->label('Sector'),
                Tables\Filters\Filter::make('fecha_comision')
                    ->form([
                        DatePicker::make('fecha_desde'),
                        DatePicker::make('fecha_hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->whereHas('delitos', function ($query) use ($date) {
                                    $query->whereDate('fecha_comision', '>=', $date);
                                })
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereHas('delitos', function ($query) use ($date) {
                                    $query->whereDate('fecha_comision', '<=', $date);
                                })
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
            'index' => Pages\ListDelincuentes::route('/'),
            'create' => Pages\CreateDelincuente::route('/create'),
            'edit' => Pages\EditDelincuente::route('/{record}/edit'),
        ];
    }
}
