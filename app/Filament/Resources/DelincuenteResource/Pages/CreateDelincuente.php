<?php

namespace App\Filament\Resources\DelincuenteResource\Pages;

use App\Filament\Resources\DelincuenteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDelincuente extends CreateRecord
{
    protected static string $resource = DelincuenteResource::class;

    // Escuchar eventos Livewire desde el mapa
    protected $listeners = ['setAddress' => 'setAddress'];

    // Método para actualizar el campo de dirección desde el mapa
    public function setAddress($address, $field)
    {
        $this->form->fill([$field => $address]);
        $this->emit('addressUpdated', $field, $address); // Notifica a JS para refrescar el input
    }
}
