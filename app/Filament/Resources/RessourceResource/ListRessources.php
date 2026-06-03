<?php

namespace Modules\Resa\Filament\Resources\RessourceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\RessourceResource;

class ListRessources extends ListRecords
{
    protected static string $resource = RessourceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
