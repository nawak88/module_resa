<?php

namespace Modules\Resa\Filament\Resources\ReservationResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\Resa\Filament\Resources\ReservationResource;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $userIds = $data['data']['users'] ?? [];
        if (empty($userIds) && ! empty($data['user_id'])) {
            $userIds = [$data['user_id']];
        }
        $data['participants'] = array_map(fn ($id) => ['user_id' => $id], array_filter($userIds));
        if (empty($data['participants'])) {
            $data['participants'] = [['user_id' => $data['user_id'] ?? null]];
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $participants = collect($data['participants'] ?? [])
            ->pluck('user_id')->filter()->unique()->values()->toArray();
        unset($data['participants']);
        $data['user_id'] = $participants[0] ?? auth()->id();
        $data['data']    = array_merge($data['data'] ?? [], ['users' => $participants]);
        return $data;
    }
}


