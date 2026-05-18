<?php

namespace Modules\Resa\Filament\Resources\ReservationResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\Resa\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
