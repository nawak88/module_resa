<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use App\Models\User;
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

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
