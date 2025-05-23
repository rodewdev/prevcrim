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

    // Asegúrate de que user_id e institucion_id estén asignados
    if (empty($delito->user_id)) {
        $delito->user_id = auth()->id();
    }
    if (empty($delito->institucion_id)) {
        $delito->institucion_id = auth()->user()->institucion_id;
    }
    $delito->save();

    // Resto de tu código para delincuentes
    $delincuenteId = $this->data['delincuente_id'];
    $delito->delincuentes()->attach($delincuenteId, [
        'fecha_comision' => $delito->fecha,
        'observaciones' => $delito->descripcion,
    ]);
    }
}
