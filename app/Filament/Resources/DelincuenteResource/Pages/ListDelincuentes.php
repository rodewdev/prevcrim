<?php

namespace App\Filament\Resources\DelincuenteResource\Pages;

use App\Filament\Resources\DelincuenteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDelincuentes extends ListRecords
{
    protected static string $resource = DelincuenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
