# CalendrierWidget - Documentation technique

## Localisation

```
Modules/Resa/Filament/Widgets/CalendrierWidget.php
```

Etend `Guava\Calendar\Filament\CalendarWidget` (Guava v2.1.1) integrant FullCalendar dans Filament v4 via Livewire.

---

## Stack et dependances

| Dependance | Role |
|---|---|
| `Guava\Calendar\Filament\CalendarWidget` | Classe parente - FullCalendar dans Filament |
| `Modules\Resa\Models\Reservation` | Model principal - implement `Eventable` |
| `Modules\Resa\Models\SallePoste` | Postes - implement `Resourceable` - colonnes calendrier |
| `Modules\Resa\Models\Salle` | Nom de la salle active dans le heading |
| `Modules\Resa\Models\TypeCapacite` | Options CheckboxList etape 1 et labels etape 2 |
| `Modules\Resa\Services\PosteDisponibiliteService` | Algorithme de scoring des postes disponibles |
| `Modules\RH\Models\User` | Select admin pour reserver pour un tiers |

---

## Proprietes de classe

```php
protected bool $eventClickEnabled  = true;
protected bool $eventDragEnabled   = true;
protected bool $eventResizeEnabled = true;
protected bool $dateClickEnabled   = true;
protected bool $dateSelectEnabled  = true;
protected ?string $locale          = 'fr';
protected bool $useFilamentTimezone = true; // evite le decalage horaire
```

### `$useFilamentTimezone = true`

Active la gestion du fuseau horaire par Filament. Sans cette propriete, les dates transmises par FullCalendar (fuseau navigateur) ne correspondent pas aux dates stockees en base, creant un decalage de 2h selon le contexte.

---

## Proprietes Livewire publiques

Toutes sont reactives et persistent entre les requetes Livewire.

| Propriete | Type | Description |
|---|---|---|
| `$salleActive` | `?int` | ID de la salle affichee. `null` = ecran selection salle |
| `$adminMode` | `bool` | Active si permission `resa::reservation.view` |
| `$recordEnCours` | `?int` | ID de la reservation ouverte dans le modal voir |
| `$recordTitre` | `?string` | Titre sauvegarde au clic - evite le 404 apres suppression |
| `$etapeDebut` | `?string` | Debut du creneau transmis entre les etapes Wizard |
| `$etapeFin` | `?string` | Fin du creneau transmis entre les etapes Wizard |
| `$etapeCapacites` | `array` | IDs TypeCapacite choisis a l'etape 1 |
| `$etapePoste` | `?int` | Reserve - poste lu depuis `$data['poste_id']` en pratique |
| `$postesDisponibles` | `array` | Options Radio calculees dans `afterValidation` etape 1 |

---

## Initialisation - `mount()`

```php
public function mount(): void
{
    $this->adminMode = auth()->user()?->can('resa::reservation.view') ?? false;

    $salles = Salle::orderBy('ordre')->get();
    if ($salles->count() === 1) {
        $this->salleActive = $salles->first()->id;
    }
}
```

Si une seule salle existe, elle est selectionnee automatiquement. L'ecran de selection ne s'affiche pas.

---

## Rendu conditionnel - `render()`

```
adminMode = true
    CalendarWidget::render() sans ecran de selection

salleActive = null ET pas admin
    vue resa::filament.widgets.salle-selection

salleActive defini
    CalendarWidget::render() calendrier de la salle
```

---

## Ressources - `getResources()`

Retourne les `SallePoste` de la salle active. Guava appelle `toCalendarResource()` sur chaque objet.

Chaque poste devient une **colonne** dans la vue timeGrid. Le champ `ordre` determine l'ordre des colonnes.

`SallePoste` doit implementer `Guava\Calendar\Contracts\Resourceable` :

```php
public function toCalendarResource(): CalendarResource
{
    return CalendarResource::make($this->id)->title($this->libelle_court ?? '');
}
```

---

## Evenements - `getEvents()`

| Role | Reservations visibles |
|---|---|
| Non connecte | `validation = 1` uniquement |
| Connecte | `validation = 1` + ses propres reservations en attente |
| Admin | Toutes sans filtre |

`Reservation` doit implementer `Guava\Calendar\Contracts\Eventable`. Le champ `resourceId` doit etre `$this->salle_poste_id` pour lier chaque evenement a la bonne colonne.

---

## Options FullCalendar - `getOptions()`

| Option | Valeur | Description |
|---|---|---|
| `weekends` | `false` | Weekends masques |
| `businessHours` | lun-ven 08h-17h | Plage horaire autorisee |
| `selectConstraint` | `businessHours` | Selection limitee aux heures ouvrables |
| `initialView` | `timeGridWeek` | Vue semaine par defaut |
| `slotDuration` | `00:15:00` | Granularite 15 min |
| `slotMinTime` | `08:00:00` | Debut de journee |
| `slotMaxTime` | `17:00:00` | Fin de journee |
| `selectable` | `true` | Selection de plage activee |
| `editable` | `auth()->check()` | Drag/resize si connecte |

---

## Validation metier - `validerCreneau()`

Appelee a 3 endroits :

1. `afterValidation()` etape 1 du Wizard - bloque le passage a l'etape 2
2. `->action()` final - verification cote serveur avant creation
3. `onEventDrop()` et `onEventResize()` - bloque le drag/resize hors plage

Regles validees :

- Pas de reservation le week-end
- Pas de reservation dans le passe
- Fin strictement superieure au debut
- Debut entre 08h00 et 16h59
- Fin entre 08h01 et 17h00

!!! warning "Bug Carbon startOfDay - IMPORTANT"
    Utiliser `$d->copy()->startOfDay()` et NON `$d->startOfDay()`. La methode `startOfDay()` est **mutating** — elle modifie l'objet Carbon en place et remet l'heure a 00:00. Apres cet appel, `$d->hour` vaut 0 et toutes les validations horaires suivantes retournent une erreur meme pour un creneau valide.

```php
// ❌ INCORRECT - modifie $d, heure devient 00:00
if ($d->startOfDay()->lt(now()->startOfDay())) { ... }

// ✅ CORRECT - $d conserve son heure
if ($d->copy()->startOfDay()->lt(now()->startOfDay())) { ... }
```

---

## Point cle - actions dans `getHeaderActions()`

!!! warning "Guava 2.1.1 + Filament v4"
    `mountAction()` ne fonctionne que sur des actions enregistrees dans `getHeaderActions()`. Les actions declarees ailleurs ne sont pas accessibles. Toutes les actions - meme cachees - doivent etre dans ce tableau.

Les actions cachees utilisent `->extraAttributes(['style' => 'display:none'])` pour ne pas apparaitre dans l'UI tout en restant montables via `mountAction()`.

**Actions dans `getHeaderActions()` :**

| Action | Visible | Declencheur |
|---|---|---|
| `retour_salle` | oui (si salleActive) | bouton |
| `login` | oui (si non connecte) | bouton |
| `nouvelle_resa` | oui (si connecte) | bouton + `onDateClick` + `onDateSelect` |
| `voirReservation` | non (display:none) | `onEventClick` |

---

## Flux Wizard - nouvelle reservation

```
Bouton "Nouvelle reservation"
OU clic/selection sur calendrier
    mountAction('nouvelle_resa')
        |
        v
Etape 1 - Creneau et capacites
    DateTimePicker debut/fin
        minDate = today
        disabledDates = weekends sur 6 mois
        hoursStep = 1, minutesStep = 15
    CheckboxList des TypeCapacite
    afterValidation()
        validerCreneau() - bloque si invalide
        PosteDisponibiliteService::postesDisponibles()
        stocke dans $this->postesDisponibles
        |
        v
Etape 2 - Choix du poste
    schema() (closure) lit $this->postesDisponibles
    Radio avec postes tries par score
        |
        v
Etape 3 - Informations
    Select user_id (admin uniquement, searchable)
    Placeholder recap creneau
    TextInput intitule (required)
    Textarea informations
        |
        v
action() final
    $data contient TOUT - Wizard agrege toutes les etapes
    $data['poste_id'] disponible directement
    validerCreneau() finale cote serveur
    isDisponible() anti-chevauchement race condition
    Reservation::create()
    Reset des proprietes etape*
    refreshRecords()
```

### Donnees disponibles dans `$data` final

```php
$data['debut']         // etape 1
$data['fin']           // etape 1
$data['capacites']     // etape 1 - array d'IDs TypeCapacite
$data['poste_id']      // etape 2 - ID du SallePoste choisi
$data['user_id']       // etape 3 - admin uniquement
$data['libelle_court'] // etape 3
$data['libelle_long']  // etape 3
```

### Labels des postes - etape 2

| Cas | Label affiche |
|---|---|
| Aucune capacite demandee | `"Poste 1 - libre"` |
| Toutes les capacites couvertes | `"Poste 1 - toutes les capacites (2/2)"` |
| Couverture partielle | `"Poste 2 - 1/2 (manque: 20mm)"` |
| Aucun poste disponible | Placeholder "Aucun poste disponible..." |

---

## Modal voir une reservation

Ouvert par `onEventClick()` :

```php
protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
{
    $this->recordEnCours = $info->record->id;
    $this->recordTitre   = $info->record->libelle_court; // sauvegarde AVANT suppression
    $this->mountAction('voirReservation');
}
```

`$recordTitre` sauvegarde le titre au moment du clic. Il est utilise dans `->modalHeading()` pour eviter une requete sur un enregistrement supprime (bug 404).

### Actions footer

| Action | Condition |
|---|---|
| Supprimer | `isEnAttente()` + (admin ou proprietaire) + confirmation |
| Valider | Admin + `resa::validation_salle` + `isEnAttente()` |
| Invalider | Admin + `resa::validation_salle` + `isValide()` |

Apres suppression :

```php
$record->delete();
$this->recordEnCours = null;
$this->recordTitre   = null;
$this->refreshRecords();
$this->unmountAction(); // ferme le modal proprement
```

---

## Drag & Drop et Resize

| Regle | Description |
|---|---|
| Reservation validee | Bloquee pour les users, autorisee pour les admins |
| Reservation d'un autre user | Bloquee |
| Hors plage horaire ou week-end | Bloque par `validerCreneau()` |

---

## Service PosteDisponibiliteService

Enregistrer dans `ResaServiceProvider` :

```php
$this->app->singleton(PosteDisponibiliteService::class);
```

Structure de retour de `postesDisponibles()` :

```php
[
    [
        'poste'      => SallePoste,  // model avec typeCapacites eager loaded
        'score'      => 2,           // capacites couvertes
        'total'      => 2,           // capacites demandees
        'manquantes' => Collection,  // IDs des capacites non couvertes
    ],
]
```

---

## Vues Blade requises

| Vue | Description |
|---|---|
| `resa::filament.widgets.salle-selection` | Grille de selection. Doit appeler `$this->selectSalle($id)` via `wire:click` |

---

## Points d'attention pour la reprise

!!! danger "Carbon startOfDay() mutating"
    `$d->startOfDay()` modifie `$d` en place. Toujours utiliser `$d->copy()->startOfDay()` pour eviter que `$d->hour` soit remis a 0 et fausse la validation horaire.

!!! warning "Bug 404 apres suppression"
    Toujours sauvegarder `$recordTitre` au moment du clic sur l'evenement. Ne jamais appeler `Reservation::find($this->recordEnCours)?->libelle_court` dans `->modalHeading()` - `recordEnCours` est null apres suppression.

!!! warning "Type-hint valider()"
    `Reservation::valider()` ne doit pas avoir de type-hint strict sur `$admin`. `auth()->user()` retourne `App\Models\User` et non `Modules\RH\Models\User`.

    ```php
    // ✅ correct
    public function valider($admin): void
    ```

!!! warning "Wizard et donnees inter-etapes"
    `$postesDisponibles` est calcule dans `afterValidation()` etape 1 et lu dans `schema()` etape 2. C'est une propriete Livewire publique - elle persiste entre les requetes du Wizard.

!!! info "refreshRecords()"
    Methode Guava Calendar qui force le rechargement depuis `getEvents()`. A appeler apres toute creation, suppression ou changement de validation.

!!! info "getWeekendDates()"
    Calcule les dates de weekend sur 6 mois pour le `disabledDates()` des DateTimePicker. Appele au moment du rendu du formulaire. Si les performances sont un sujet, mettre le resultat en cache.
