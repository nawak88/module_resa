<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;

class ListTypeCapacites extends ListRecords
{
    protected static string $resource = TypeCapaciteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
