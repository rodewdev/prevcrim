<?php

namespace App\Filament\Resources\DelincuenteResource\Pages;

use App\Filament\Resources\DelincuenteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelincuente extends EditRecord
{
    protected static string $resource = DelincuenteResource::class;

    // Escuchar eventos Livewire desde el mapa
    protected $listeners = ['setAddress' => 'setAddress'];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Método para actualizar el campo de dirección desde el mapa
    public function setAddress($address, $field)
    {
        $this->form->fill([$field => $address]);
        $this->emit('addressUpdated', $field, $address); // Notifica a JS para refrescar el input
    }
}
