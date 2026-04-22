<?php

namespace Modules\Resa\Filament\Resources\SallePostes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\SallePostes\SallePosteResource;

class ListSallePostes extends ListRecords
{
    protected static string $resource = SallePosteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
