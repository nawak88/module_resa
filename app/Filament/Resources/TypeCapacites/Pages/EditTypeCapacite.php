<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;
use Modules\Resa\Models\Formation;
use Spatie\Permission\Models\Permission;

class EditTypeCapacite extends EditRecord
{
    protected static string $resource = TypeCapaciteResource::class;

    /** Gestionnaires de la formation avant sauvegarde, pour détecter qui a été retiré. */
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
            ['name' => 'resa::gestionnaire_formation', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneFormation = Formation::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneFormation) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
