<?php

namespace Modules\Resa\Filament\Resources\TypeSalles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\TypeSalles\TypeSalleResource;

class EditTypeSalle extends EditRecord
{
    protected static string $resource = TypeSalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
