<?php

namespace Modules\Resa\Filament\Components;

use Filament\Forms\Components\TagsInput;

/**
 * Champ "liste d'emails" affiché comme des badges (ajout/suppression individuelle),
 * tout en restant stocké en base comme une simple chaîne séparée par des virgules
 * (colonne `email` existante sur Salle/Formation — pas de migration nécessaire).
 */
class EmailListField
{
    public static function make(string $name = 'email'): TagsInput
    {
        return TagsInput::make($name)
            ->label('Email(s) de notification')
            ->helperText('Un email est envoyé à chaque nouvelle réservation. Tapez une adresse puis Entrée pour l\'ajouter ; cliquez sur le × pour en retirer une.')
            ->placeholder('Ajouter une adresse email…')
            ->separator(',')
            ->afterStateHydrated(fn (TagsInput $component, $state) => $component->state(self::toArray($state)))
            ->dehydrateStateUsing(fn ($state) => self::toString($state))
            ->columnSpanFull();
    }

    /**
     * Chaîne séparée par des virgules (valeur brute en base) → liste d'emails nettoyée.
     * Accepte aussi directement un tableau (Filament peut rappeler afterStateHydrated()
     * avec un état déjà transformé lors d'un précédent cycle) : renvoyé tel quel, nettoyé.
     */
    public static function toArray(array|string|null $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    /** Liste d'emails (état du TagsInput) → chaîne séparée par des virgules pour la base. */
    public static function toString(?array $value): string
    {
        return implode(',', array_filter((array) $value));
    }
}
