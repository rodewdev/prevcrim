<?php

namespace App\Filament\Resources\DelitoResource\Pages;

use App\Filament\Resources\DelitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDelitos extends ListRecords
{
    protected static string $resource = DelitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
