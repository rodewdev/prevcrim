<?php

namespace App\Filament\Resources\CodigoDelitoResource\Pages;

use App\Filament\Resources\CodigoDelitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCodigoDelito extends EditRecord
{
    protected static string $resource = CodigoDelitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
