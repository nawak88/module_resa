<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use Filament\Actions\CreateAction;
use Modules\Resa\Filament\Resources\SalleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalles extends ListRecords
{
    protected static string $resource = SalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
