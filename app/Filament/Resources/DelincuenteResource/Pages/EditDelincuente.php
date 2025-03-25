<?php

namespace App\Filament\Resources\DelincuenteResource\Pages;

use App\Filament\Resources\DelincuenteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelincuente extends EditRecord
{
    protected static string $resource = DelincuenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
