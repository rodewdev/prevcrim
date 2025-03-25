<?php

namespace App\Filament\Resources\CodigoDelitoResource\Pages;

use App\Filament\Resources\CodigoDelitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCodigoDelitos extends ListRecords
{
    protected static string $resource = CodigoDelitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
