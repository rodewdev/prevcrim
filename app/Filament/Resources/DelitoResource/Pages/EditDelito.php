<?php

namespace App\Filament\Resources\DelitoResource\Pages;

use App\Filament\Resources\DelitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelito extends EditRecord
{
    protected static string $resource = DelitoResource::class;
    protected function afterSave(): void
    {
        $delito = $this->record;
        $delincuenteId = $this->data['delincuente_id'];

        $delito->delincuentes()->sync([$delincuenteId => [
            'fecha_comision' => $delito->fecha,
            'observaciones' => $delito->descripcion,
        ]]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
