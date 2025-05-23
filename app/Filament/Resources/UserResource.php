<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Rules\RutChileno;
use Illuminate\Database\Eloquent\Model; 
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'Jefe de zona']);
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'Jefe de zona']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rut')
                ->label('RUT')
                ->unique(User::class, 'rut', fn ($record) => $record)
                ->maxLength(12)
                ->rules([new RutChileno()])
                ->live()
                ->afterStateUpdated(function (?string $state, Forms\Components\TextInput $component, $set, $livewire) {
                    // Si el estado es null, no procesar
                    if (!$state) {
                        return;
                    }
                    
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->required(fn($context) => $context === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options(Role::pluck('name', 'name'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('institucion_id')
                    ->label('Institución')
                    ->relationship('institucion', 'nombre')
                    ->searchable()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rut')->label('RUT')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Rol')->sortable(),
                Tables\Columns\TextColumn::make('institucion.nombre')->label('Institución')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // Hook para asignar el rol seleccionado
    public static function afterSave($record, $data)
    {
        if (isset($data['role'])) {
            $record->syncRoles([$data['role']]);
        }
    }
}