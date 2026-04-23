# CalendrierWidget - Documentation technique

## Localisation

```
Modules/Resa/Filament/Widgets/CalendrierWidget.php
Modules/Resa/Filament/Widgets/CalendrierWidget/Actions/
    NouvelleResaAction.php
    NouvelleResaClassiqueAction.php
    VoirReservationAction.php
    FiltresAction.php
```

Etend `Guava\Calendar\Filament\CalendarWidget` (Guava v2.1.1) integrant FullCalendar dans Filament v4 via Livewire.

---

## Stack et dependances

| Dependance | Role |
|---|---|
| `Guava\Calendar\Filament\CalendarWidget` | Classe parente — FullCalendar dans Filament |
| `Modules\Resa\Models\Reservation` | Model principal — implement `Eventable` |
| `Modules\Resa\Models\SallePoste` | Postes — implement `Resourceable` — colonnes calendrier |
| `Modules\Resa\Models\Salle` | Nom de la salle active dans le heading |
| `Modules\Resa\Models\TypeSalle` | Mode simulateur/classique + navigation |
| `Modules\Resa\Models\TypeCapacite` | Options CheckboxList etape 1 et labels etape 2 |
| `Modules\Resa\Services\PosteDisponibiliteService` | Algorithme de scoring des postes disponibles |
| `App\Models\User` | Select admin pour reserver pour un tiers |

---

## Proprietes Livewire publiques

| Propriete | Type | Description |
|---|---|---|
| `$etapeNavigation` | `string` | `type_salle` / `salle` / `calendrier` |
| `$typeSalleActive` | `?int` | ID du TypeSalle selectionne |
| `$salleActive` | `?int` | ID de la salle affichee |
| `$adminMode` | `bool` | Active si permission `resa::reservation.view` |
| `$recordEnCours` | `?int` | ID de la reservation ouverte dans le modal |
| `$recordTitre` | `?string` | Titre sauvegarde au clic — evite le 404 apres suppression |
| `$etapeDebut` | `?string` | Debut du creneau transmis entre les etapes Wizard |
| `$etapeFin` | `?string` | Fin du creneau transmis entre les etapes Wizard |
| `$etapeCapacites` | `array` | IDs TypeCapacite choisis a l'etape 1 (simulateur) |
| `$etapePoste` | `?int` | Reserve |
| `$postesDisponibles` | `array` | Options Radio calculees dans `afterValidation` etape 1 |
| `$filtrePostes` | `array` | IDs SallePoste filtres (simulateur uniquement) |
| `$filtreCapacites` | `array` | IDs TypeCapacite filtres (simulateur uniquement) |

---

## Navigation — `resoudreNavigation()`

Appelee dans `mount()`. Determine automatiquement le point d'entree selon la configuration.

```
1 seule salle au total
    → salleActive = salle.id
    → typeSalleActive = salle.type_salle_id
    → etapeNavigation = 'calendrier'

Plusieurs salles, 1 seul TypeSalle
    → typeSalleActive = typeSalle.id
    → etapeNavigation = 'salle'

Plusieurs TypeSalles
    → etapeNavigation = 'type_salle'
```

```php
public function selectTypeSalle(int $id): void
// Si 1 seule salle dans ce type → calendrier direct
// Sinon → liste des salles

public function selectSalle(int $id): void
// → etapeNavigation = 'calendrier'

public function retourSalles(): void
// → etapeNavigation = 'salle'

public function retourTypeSalles(): void
// → etapeNavigation = 'type_salle'
```

---

## Rendu conditionnel — `render()`

```php
match ($this->etapeNavigation) {
    'type_salle' => view('resa::filament.widgets.type-salle-selection', [...]),
    'salle'      => view('resa::filament.widgets.salle-selection', [...]),
    default      => parent::render(), // calendrier FullCalendar
}
```

### Vues Blade requises

| Vue | Declencheur Livewire |
|---|---|
| `resa::filament.widgets.type-salle-selection` | `wire:click="selectTypeSalle($id)"` |
| `resa::filament.widgets.salle-selection` | `wire:click="selectSalle($id)"`, `wire:click="retourTypeSalles"` |

---

## Mode actif

```php
public function getModeActif(): string  // simulateur | classique
public function isModeClassique(): bool
public function isModeSimulateur(): bool
```

Le mode est lu depuis `TypeSalle::find($this->typeSalleActive)->mode`.

---

## Ressources — `getResources()`

```php
// Mode classique → pas de colonnes
if ($this->isModeClassique()) return [];

// Mode simulateur → postes reels, sans le poste fictif
return SallePoste::query()
    ->where('salle_id', $this->salleActive)
    ->where('libelle_court', '!=', 'Salle entiere')
    ->orderBy('ordre')
    ->get();
```

---

## Evenements — `getEvents()`

| Role | Reservations visibles |
|---|---|
| Non connecte | `validation = 1` uniquement |
| Connecte | `validation = 1` + ses propres reservations en attente |
| Admin | Toutes sans filtre |

Filtres additionnels (mode simulateur) : `filtrePostes` (`whereIn`) et `filtreCapacites` (`whereJsonContains`).

---

## Deux Wizards selon le mode

### Mode Simulateur — `NouvelleResaAction`

```
Etape 1 - Creneau et capacites
    DateTimePicker debut/fin
    CheckboxList TypeCapacite
    afterValidation()
        validerCreneauPublic() — bloque + ValidationException si invalide
        PosteDisponibiliteService::postesDisponibles()
        → $this->postesDisponibles

Etape 2 - Choix du poste
    Radio avec postes tries par score

Etape 3 - Informations
    Select user_id (admin uniquement)
    TextInput libelle_court
    Textarea libelle_long

action() final
    validerCreneauPublic() cote serveur
    isDisponible() anti-race-condition
    Reservation::create()
```

### Mode Classique — `NouvelleResaClassiqueAction`

```
Etape 1 - Creneau
    DateTimePicker debut/fin
    TextEntry capacite de la salle (indicatif)
    afterValidation()
        validerCreneauPublic()
        isDisponible() sur le poste fictif "Salle entiere"
        → bloque si salle deja reservee

Etape 2 - Informations
    TextEntry recap creneau
    TextInput libelle_court
    Textarea libelle_long

action() final
    Recupere le SallePoste "Salle entiere" de la salle active
    isDisponible() anti-race-condition
    Reservation::create() avec capacites_utilisees = []
```

---

## Header actions

| Action | Mode | Visible | Declencheur |
|---|---|---|---|
| `retour_salle` | tous | si plusieurs salles | bouton |
| `retour_type_salle` | tous | si plusieurs types + 1 salle | bouton |
| `login` | tous | si non connecte | bouton |
| `filtres` | simulateur | si connecte | bouton |
| `nouvelle_resa` | selon mode | si connecte | bouton + `onDateClick` + `onDateSelect` |
| `voirReservation` | tous | non (display:none) | `onEventClick` |

---

## Validation metier — `validerCreneauPublic()`

Appelee dans `afterValidation()` etape 1, `action()` final, `onEventDrop()` et `onEventResize()`.

Regles :
- Pas le week-end
- Pas dans le passe
- Fin > debut
- Debut entre 08h00 et 16h59
- Fin entre 08h01 et 17h00
- Un user ne peut pas avoir deux reservations qui se chevauchent

!!! warning "Bug Carbon startOfDay() mutating"
    Toujours `$d->copy()->startOfDay()` et non `$d->startOfDay()`.
    `startOfDay()` modifie `$d` en place — l'heure devient 00:00 et toutes les validations horaires suivantes echouent.

---

## Blocage visuel creneaux — `selectAllow`

Les reservations existantes du user connecte sont serialisees en JSON et passees a FullCalendar via `getOptions()`. `selectAllow` en JS verifie le chevauchement et retourne `false` pour bloquer visuellement la selection avant meme d'ouvrir le Wizard.

---

## Affichage des evenements

Le titre est construit dans `Reservation::toCalendarEvent()` avec `implode("\n", $lignes)` :

```
09:00 - 11:00
Entrainement FPV
👤 Boisson (admin uniquement)
🖥 Poste 1
🎯 FPV, 20mm
```

`extendedProps` transmis a FullCalendar :
- `nom` — renseigne uniquement si `$isAdmin`
- `poste` — `libelle_court` du SallePoste
- `capacites_label` — libelles des capacites jointes
- `heure_debut` / `heure_fin` — format `H:i`

---

## Points d'attention

!!! danger "Carbon startOfDay() mutating"
    `$d->startOfDay()` modifie `$d` en place. Toujours `$d->copy()->startOfDay()`.

!!! warning "Bug 404 apres suppression"
    Sauvegarder `$recordTitre` au clic. Ne jamais appeler `Reservation::find($this->recordEnCours)` dans `->modalHeading()`.

!!! warning "Type-hint valider()"
    `Reservation::valider()` sans type-hint strict sur `$admin`.

!!! warning "Wizard donnees inter-etapes"
    `$postesDisponibles` est une propriete Livewire publique — elle persiste entre les requetes du Wizard.

!!! warning "eventContent() — NE PAS UTILISER"
    `eventContent()` avec Alpine.js casse le `onClick` de Guava v2.1.1. Utiliser `implode("\n", ...)` dans `title()` a la place.

!!! warning "eventDidMount — NE PAS UTILISER"
    Meme probleme que `eventContent()`. Casse le handler `onClick` Alpine.

!!! info "Poste fictif mode classique"
    Le poste "Salle entiere" est exclu de `getResources()` via `where('libelle_court', '!=', 'Salle entiere')`.
    Il est utilise uniquement comme `salle_poste_id` sur les reservations classiques.

!!! info "refreshRecords()"
    Methode Guava qui force le rechargement depuis `getEvents()`. Appeler apres toute creation, suppression ou changement de validation.
