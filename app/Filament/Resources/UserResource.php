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
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona']);
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['Administrador General']);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasRole(['Administrador General']);
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isJefeZona = $user && $user->hasRole('Jefe de Zona');
        $isPazCiudadana = $user && $user->institucion && str_contains(strtolower($user->institucion->nombre), 'paz ciudadana');
        $isPDI = $user && $user->institucion && str_contains(strtolower($user->institucion->nombre), 'polic'); // para Policía de Investigaciones
        $isAdmin = $user && $user->hasRole('Administrador General');
        $roleOptions = Role::pluck('name', 'name');
        if (($isPazCiudadana || $isPDI) && !$isAdmin) {
            $roleOptions = $roleOptions->only(['Jefe de Zona', 'Operador']);
        }
        return $form
            ->schema([
                Forms\Components\TextInput::make('rut')
                ->label('RUT')
                ->unique(User::class, 'rut', fn ($record) => $record)
                ->maxLength(12)
                ->required()
                ->placeholder('11.111.111-1')
                ->helperText('Ingrese un RUT chileno válido con formato XX.XXX.XXX-X')
                ->rules([new RutChileno()])
                ->live()
                ->afterStateUpdated(function (?string $state, Forms\Components\TextInput $component, $set, $livewire) {
                    // Si el estado es null, no procesar
                    if (!$state) {
                        $livewire->addError('rut', 'Ingrese un RUT válido. Ejemplo: 11.111.111-1');
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
                        $livewire->addError('rut', 'Ingrese un RUT válido. Ejemplo: 11.111.111-1');
                    }
                }),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nombre y Apellido')
                    ->helperText('Ingrese nombres y apellidos (solo letras, espacios y tildes)')
                    ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/')
                    ->validationMessages([
                        'regex' => 'El nombre solo puede contener letras, espacios y tildes.',
                    ]),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->placeholder('usuario@dominio.com')
                    ->helperText('Ingrese un correo electrónico válido')
                    ->maxLength(255)
                    ->validationMessages([
                        'email' => 'Ingrese un email válido. Ejemplo: mail@mail.com',
                    ]),
                Forms\Components\Hidden::make('email_verified_at')
                    ->default(now()),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->required(fn($context) => $context === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->helperText('La contraseña debe tener al menos 8 caracteres')
                    ->validationMessages([
                        'min' => 'La contraseña debe tener al menos 8 caracteres',
                    ]),
                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options($roleOptions)
                    ->searchable()
                    ->required()
                    ->placeholder('Seleccione un rol')
                    ->validationMessages([
                        'required' => 'Debe seleccionar un rol',
                    ]),
                Forms\Components\Select::make('institucion_id')
                    ->label('Institución')
                    ->relationship('institucion', 'nombre')
                    ->searchable()
                    ->nullable()
                    ->placeholder('Seleccione una institución (opcional)')
                    ->default($isJefeZona ? $user->institucion_id : null)
                    ->disabled($isJefeZona)
                    ->hidden($isJefeZona),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                
                // Si es Administrador General, puede ver todos los usuarios
                if ($user->hasRole('Administrador General')) {
                    return $query;
                }
                
                // Si no es admin, solo muestra usuarios de su misma institución
                if ($user->institucion_id) {
                    return $query->where('institucion_id', $user->institucion_id);
                }
                
                return $query;
            })
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
                Tables\Filters\SelectFilter::make('institucion_id')
                    ->label('Institución')
                    ->relationship('institucion', 'nombre')
                    ->visible(fn () => auth()->user()->hasRole('Administrador General')),
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