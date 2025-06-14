<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function afterCreate(): void
    {
        // Asignar el rol seleccionado
        if ($this->data['role'] ?? false) {
            $this->record->syncRoles([$this->data['role']]);
        }
        
        // Asignar automáticamente la institución si el usuario actual (Jefe de Zona) tiene una institución
        $user = auth()->user();
        if ($user && $user->institucion_id && !$user->hasRole('Administrador General')) {
            $this->record->institucion_id = $user->institucion_id;
            $this->record->save();
        }
    }
}
