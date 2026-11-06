<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\SalleResource;
use Modules\Resa\Models\Salle;
use Spatie\Permission\Models\Permission;

class EditSalle extends EditRecord
{
    protected static string $resource = SalleResource::class;

    /** Gestionnaires de la salle avant sauvegarde, pour détecter qui a été retiré. */
    private array $anciensGestionnairesIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->anciensGestionnairesIds = $this->record->gestionnaires()->pluck('user_id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneSalle = Salle::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneSalle) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
