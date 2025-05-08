<?php

namespace App\Filament\Resources\DelitoResource\Pages;

use App\Filament\Resources\DelitoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDelito extends CreateRecord
{
    protected static string $resource = DelitoResource::class;

    protected function afterCreate(): void
    {
        $delito = $this->record;
        $delincuenteId = $this->data['delincuente_id'];
        $delito->delincuentes()->attach($delincuenteId, [
            'fecha_comision' => $delito->fecha,
            'observaciones' => $delito->descripcion,
        ]);
    }
}
