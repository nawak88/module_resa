<?php

namespace Modules\Resa\Filament\Resources\RessourceReservationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\RessourceReservationResource;

class ListRessourceReservations extends ListRecords
{
    protected static string $resource = RessourceReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}


