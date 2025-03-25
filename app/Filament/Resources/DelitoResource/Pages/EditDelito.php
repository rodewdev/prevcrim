<?php

namespace App\Filament\Resources\DelitoResource\Pages;

use App\Filament\Resources\DelitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelito extends EditRecord
{
    protected static string $resource = DelitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
