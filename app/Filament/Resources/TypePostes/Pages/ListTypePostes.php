<?php

namespace Modules\Resa\Filament\Resources\TypePostes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\TypePostes\TypePosteResource;

class ListTypePostes extends ListRecords
{
    protected static string $resource = TypePosteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
