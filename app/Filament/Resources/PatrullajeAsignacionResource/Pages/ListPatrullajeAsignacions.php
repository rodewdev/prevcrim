<?php

namespace App\Filament\Resources\PatrullajeAsignacionResource\Pages;

use App\Filament\Resources\PatrullajeAsignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatrullajeAsignacions extends ListRecords
{
    protected static string $resource = PatrullajeAsignacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
