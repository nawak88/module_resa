<?php

namespace Modules\Resa\Filament\Resources\SallePostes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\SallePostes\SallePosteResource;

class EditSallePoste extends EditRecord
{
    protected static string $resource = SallePosteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
