<?php

namespace Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\RessourceCategorieResource;
use Spatie\Permission\Models\Permission;

class CreateRessourceCategorie extends CreateRecord
{
    protected static string $resource = RessourceCategorieResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_ressource', 'guard_name' => 'web']
        );

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
