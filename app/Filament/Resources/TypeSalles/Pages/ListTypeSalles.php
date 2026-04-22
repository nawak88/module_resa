<?php

namespace Modules\Resa\Filament\Resources\TypeSalles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\TypeSalles\TypeSalleResource;

class ListTypeSalles extends ListRecords
{
    protected static string $resource = TypeSalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
