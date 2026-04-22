<?php

namespace Modules\Resa\Filament\Resources\TypePostes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\TypePostes\TypePosteResource;

class EditTypePoste extends EditRecord
{
    protected static string $resource = TypePosteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
