<?php

namespace Modules\Resa\Filament\Resources\SallePostes\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Resa\Filament\Resources\SallePostes\SallePosteResource;

class ViewSallePoste extends ViewRecord
{
    protected static string $resource = SallePosteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
