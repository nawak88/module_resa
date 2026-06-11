<?php

namespace Modules\Resa\Filament\Resources\RessourceReservationResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\RessourceReservationResource;

class EditRessourceReservation extends EditRecord
{
    protected static string $resource = RessourceReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
