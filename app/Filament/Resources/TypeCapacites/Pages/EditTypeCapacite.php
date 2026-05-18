<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;

class EditTypeCapacite extends EditRecord
{
    protected static string $resource = TypeCapaciteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
