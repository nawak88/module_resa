<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceReservation;

class NouvelleResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('nouvelleResa')
            ->label('Nouvelle réservation')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->visible(fn () => auth()->check())
            ->schema(self::schema($widget))
            ->action(fn (array $data) => self::creerReservation($widget, $data))
            ->fillForm(fn () => [
                'ressource_id' => $widget->newRessourceId,
                'debut'        => $widget->newDebut,
                'fin'          => $widget->newFin,
            ]);
    }

    private static function schema(object $widget): array
    {
        return [
            Select::make('ressource_id')
                ->label('Ressource')
                ->options(fn () => $widget->getResources()->pluck('nom', 'id'))
                ->required()
                ->searchable(),

            DateTimePicker::make('debut')
                ->label('Début')
                ->required()
                ->seconds(false),

            DateTimePicker::make('fin')
                ->label('Fin')
                ->required()
                ->seconds(false)
                ->after('debut'),

            Textarea::make('libelle')
                ->label('Objet')
                ->rows(2),

            TextInput::make('batiment')
                ->label('Bâtiment')
                ->required()
                ->maxLength(255),

            TextInput::make('service')
                ->label('Service concerné')
                ->required()
                ->maxLength(255),

            Select::make('emprunteur_user_id')
                ->label('Emprunteur')
                ->helperText('Le grade est repris automatiquement depuis son dossier RH.')
                ->options(fn () => User::orderBy('nom')->get()
                    ->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                ->searchable()
                ->required()
                ->default(auth()->id()),

            Select::make('officier_responsable_user_id')
                ->label('Officier responsable')
                ->helperText('Le grade est repris automatiquement depuis son dossier RH.')
                ->options(fn () => User::orderBy('nom')->get()
                    ->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                ->searchable()
                ->required(),
        ];
    }

    private static function creerReservation(object $widget, array $data): void
    {
        if (! auth()->check()) return;

        $ressource = Ressource::find($data['ressource_id']);
        if (! $ressource?->isDisponible()) {
            Notification::make()->title('Ressource indisponible')
                ->body('Cette ressource est actuellement ' . ($ressource?->statut_label ?? 'indisponible') . ' et ne peut pas être réservée.')
                ->danger()->send();
            return;
        }

        $conflit = RessourceReservation::where('ressource_id', $data['ressource_id'])
            ->where('debut', '<', $data['fin'])
            ->where('fin',   '>', $data['debut'])
            ->exists();

        if ($conflit) {
            Notification::make()->title('Conflit de réservation')
                ->body('Cette ressource est déjà réservée sur ce créneau.')
                ->danger()->send();
            return;
        }

        $gestionnaireCategorie = auth()->user()->can('resa::gestionnaire_ressource')
            && $ressource->categorie?->isGestionnaire(auth()->user());
        $valideDirectement = $widget->adminMode || $gestionnaireCategorie;

        RessourceReservation::create([
            'user_id'                      => auth()->id(),
            'ressource_id'                 => $data['ressource_id'],
            'libelle'                      => $data['libelle'] ?? null,
            'debut'                        => $data['debut'],
            'fin'                          => $data['fin'],
            'batiment'                     => $data['batiment'] ?? null,
            'service'                      => $data['service'] ?? null,
            'emprunteur_user_id'           => $data['emprunteur_user_id'] ?? null,
            'officier_responsable_user_id' => $data['officier_responsable_user_id'] ?? null,
            'validation'                   => $valideDirectement ? RessourceReservation::VALIDE : RessourceReservation::EN_ATTENTE,
            'validated_by'                 => $valideDirectement ? auth()->id() : null,
            'validated_at'                 => $valideDirectement ? now() : null,
        ]);

        $widget->refreshRecords();
        $widget->newDebut       = null;
        $widget->newFin         = null;
        $widget->newRessourceId = null;
    }
}
