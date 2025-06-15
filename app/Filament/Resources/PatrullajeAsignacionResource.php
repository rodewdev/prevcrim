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
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model; 
class PatrullajeAsignacionResource extends Resource
{
    protected static ?string $model = PatrullajeAsignacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Asignación de Patrullaje';

      public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }
    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona','Operador']);
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasRole(['Administrador General', 'Jefe de Zona', 'Operador']);
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
         return auth()->user()->hasRole(['Administrador General']);    
    }
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('region_id')
    ->label('Región')
    ->options(function() {
        $query = DB::table('comunas as c')
            ->join('regiones as r', 'c.region_id', '=', 'r.id')  
            ->join('delitos as d', 'd.comuna_id', '=', 'c.id');
        
        // Si NO es Admin General, filtrar por institución
        if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
            $query->where('d.institucion_id', auth()->user()->institucion_id);
        }
        
        return $query->select('r.id', 'r.nombre')
            ->distinct()
            ->pluck('nombre', 'id')
            ->toArray();
    })
    ->required()
    ->afterStateUpdated(fn ($state, $set) => $set('comuna_id', null)),

            // Selector de COMUNA filtrado por región
            Forms\Components\Select::make('comuna_id')
                ->label('Comuna')
                ->options(function ($get) {
                    $regionId = $get('region_id');
                    if (!$regionId) return [];
                    
                    // Consulta base para comunas con delitos de la región seleccionada
                    $query = DB::table('comunas as c')
                        ->join('delitos as d', 'd.comuna_id', '=', 'c.id')
                        ->where('c.region_id', $regionId);
                    
                    // Si NO es Admin General, filtrar por institución
                    if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        $query->where('d.institucion_id', auth()->user()->institucion_id);
                    }
                    
                    return $query->select('c.id', 'c.nombre')
                        ->distinct()
                        ->pluck('nombre', 'id')
                        ->toArray();
                })
                ->required()
                ->searchable()
                ->afterStateUpdated(fn ($state, $set) => $set('sector_id', null)),

            // Selector de SECTOR filtrado por comuna
            Forms\Components\Select::make('sector_id')
                ->label('Sector')
                ->options(function ($get) {
                    $comunaId = $get('comuna_id');
                    if (!$comunaId) return [];
                    
                    // Consulta base a delitos con JOIN a sectores
                    $query = DB::table('delitos as d')
                        ->join('sectores as s', 'd.sector_id', '=', 's.id')
                        ->join('comunas as c', 'd.comuna_id', '=', 'c.id')
                        ->where('d.comuna_id', $comunaId);
                        
                    // Si NO es Admin General, filtrar por institución
                    if (!auth()->user()->hasRole(['Administrador General', 'Super Admin'])) {
                        $query->where('d.institucion_id', auth()->user()->institucion_id);
                    }
                    
                    $sectoresConDelitos = $query->select('s.id', 's.nombre', DB::raw('count(*) as total_delitos'))
                        ->groupBy('s.id', 's.nombre')
                        ->having(DB::raw('count(*)'), '>=', 1) // Al menos 1 delito
                        ->pluck('nombre', 'id')
                        ->toArray();
                    
                    // Si no hay sectores con delitos, mostrar mensaje
                    if (empty($sectoresConDelitos)) {
                        return ['0' => 'No hay sectores con delitos registrados para su institución'];
                    }
                    
                    return $sectoresConDelitos;
                })
                ->required()
                ->helperText('Se muestran sectores con al menos 1 delito registrado de su institución'),

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
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),

            Forms\Components\Hidden::make('institucion_id')
                ->default(fn() => auth()->user()->institucion_id),

            Forms\Components\Hidden::make('user_id')
                ->default(fn() => auth()->id()),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si el usuario es Admin General, muestra todo
        if (auth()->user()->hasRole('Administrador General') || auth()->user()->hasRole('Super Admin')) {
            return $query;
        }
        
        // Si NO, solo muestra asignaciones de patrullaje de su institución
        return $query->where('institucion_id', auth()->user()->institucion_id);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comuna.nombre')
                    ->label('Comuna')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sector.nombre')
                    ->label('Sector')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('prioridad')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja',
                        default => 'No definida',
                    }),
                    
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date(),
                    
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date()
                    ->placeholder('No definida'),
                    
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
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