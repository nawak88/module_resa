<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\SalleResource;
use Spatie\Permission\Models\Permission;

class EditSalle extends EditRecord
{
    protected static string $resource = SalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->attribuerPermissionGestionnaires();
    }

    private function attribuerPermissionGestionnaires(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']
        );

        $this->record->gestionnaires->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
