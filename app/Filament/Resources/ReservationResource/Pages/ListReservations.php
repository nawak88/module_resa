<?php

namespace Modules\Resa\Filament\Resources\ReservationResource\Pages;

use Filament\Actions\CreateAction;
use Modules\Resa\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
