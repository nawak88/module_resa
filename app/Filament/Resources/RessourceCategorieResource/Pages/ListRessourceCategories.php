<?php

namespace Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resa\Filament\Resources\RessourceCategorieResource;

class ListRessourceCategories extends ListRecords
{
    protected static string $resource = RessourceCategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
