<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;
use Spatie\Permission\Models\Permission;

class CreateTypeCapacite extends CreateRecord
{
    protected static string $resource = TypeCapaciteResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_formation', 'guard_name' => 'web']
        );

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
