<?php

namespace Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\RessourceCategorieResource;
use Modules\Resa\Models\RessourceCategorie;
use Spatie\Permission\Models\Permission;

class EditRessourceCategorie extends EditRecord
{
    protected static string $resource = RessourceCategorieResource::class;

    /** Gestionnaires de la catégorie avant sauvegarde, pour détecter qui a été retiré. */
    private array $anciensGestionnairesIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->anciensGestionnairesIds = $this->record->gestionnaires()->pluck('user_id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_ressource', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneCategorie = RessourceCategorie::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneCategorie) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
