<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\SalleResource;
use Spatie\Permission\Models\Permission;

class CreateSalle extends CreateRecord
{
    protected static string $resource = SalleResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']
        );

        $this->record->gestionnaires->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
