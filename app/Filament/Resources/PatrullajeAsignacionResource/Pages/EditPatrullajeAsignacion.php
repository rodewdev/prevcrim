<?php

namespace App\Filament\Resources\PatrullajeAsignacionResource\Pages;

use App\Filament\Resources\PatrullajeAsignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatrullajeAsignacion extends EditRecord
{
    protected static string $resource = PatrullajeAsignacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
