<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\Resa\Filament\Resources\SalleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalle extends EditRecord
{
    protected static string $resource = SalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
