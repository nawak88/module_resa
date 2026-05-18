# Tests CalendrierWidget - Documentation

## Localisation

```
Modules/Resa/tests/Feature/ResaTest.php
```

---

## Lancer les tests

```bash
# Tous les tests du module Resa
./vendor/bin/pest --group=Resa

# Un seul describe
./vendor/bin/pest --filter="Visiteur non connecte"
./vendor/bin/pest --filter="Utilisateur connecte"
./vendor/bin/pest --filter="Admin"
./vendor/bin/pest --filter="Salle classique"

# Verbose
./vendor/bin/pest --group=Resa --verbose
```

---

## Structure des tests — 50+ tests

Les tests sont organises en 7 groupes dans 3 fichiers.

### `ResaTest.php` — comportement calendrier

#### Visiteur non connecte (3 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit le widget | Le composant Livewire se rend sans erreur |
| Ne voit pas le bouton de reservation | "Se connecter pour reserver" present, "Nouvelle reservation" absent |
| Voit uniquement les reservations validees | `validation = 1` visible, `validation = 0` masquee |

#### Utilisateur connecte (9 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit le bouton nouvelle reservation | Bouton present si connecte |
| Voit ses reservations en attente | Ses propres en attente visibles, celles des autres non |
| Peut creer une reservation | `validation = 0` |
| Ne peut pas reserver sur creneau pris | `isDisponible()` bloque |
| Peut supprimer sa reservation en attente | Suppression OK |
| Ne peut pas supprimer une validee | `canModify = false` |
| Ne peut pas reserver le week-end | `validerCreneau()` retourne erreur |
| Ne peut pas reserver avant 08h00 | Heure invalide |
| Ne peut pas reserver apres 17h00 | Heure invalide |

#### Admin (5 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit toutes les reservations | Pas de filtre `validation` |
| Peut valider | `validation = 1`, `validated_by` renseigne |
| Peut invalider | `validation = 0`, `validated_by = null` |
| Peut reserver pour un tiers | `user_id` != admin, directement validee |
| Peut supprimer une validee | Suppression OK |

#### Salle classique (9 tests)

| Test | Ce qui est verifie |
|---|---|
| Mode classique detecte | `isClassique() = true` |
| Poste fictif system | `isSystem() = true` |
| Reservation salle entiere libre | `isDisponible() = true` |
| Chevauchement bloque | `isDisponible() = false` |
| Deux users simultanes bloques | Second `isDisponible() = false` |
| Creneaux non chevauchants OK | Matin + apres-midi |
| `capacites_utilisees` vides | `[]` en mode classique |
| Poste fictif hors ressources | Exclu de `getResources()` |
| Admin valide reservation classique | `valider()` fonctionne |

#### CalendrierWidget — navigation lieu et unite (7 tests)

| Test | Ce qui est verifie |
|---|---|
| Plusieurs lieux → choix lieu affiché | `etapeNavigation = 'lieu'` |
| 1 seul lieu, 2 unites → saute à unite | `lieuActive` positionne, `etapeNavigation = 'unite'` |
| 1 seul lieu, 1 seule unite → saute à salle | `lieuActive`, `uniteActive` positionnes |
| Aucune salle avec `unite_id` → flow classique | `lieuActive = null`, `uniteActive = null` |
| Plusieurs lieux + 1 lieu/1 unite → saute lieu et unite | `etapeNavigation = 'salle'` apres auto-resolution |
| `selectLieu()` positionne et passe à unite | `lieuActive = id`, `etapeNavigation = 'unite'` |
| `selectUnite()` positionne et passe à l'etape suivante | `uniteActive = id` |

#### ReservationObserver — notification email (4 tests)

| Test | Ce qui est verifie |
|---|---|
| Email renseigne → 1 envoi | `Mail::assertSent(ReservationCree)` |
| Virgule → 2 envois | `Mail::assertSent(..., 2)` |
| Email null → aucun envoi | `Mail::assertNothingSent()` |
| Email vide → aucun envoi | `Mail::assertNothingSent()` |

---

### `ResourcesAdminTest.php` — acces HTTP ressources

Pages `index`, `create`, `edit` des 6 resources testees avec `actingAs($admin)` → 200.

#### Gestionnaire de salle (12 tests)

| Test | Ce qui est verifie |
|---|---|
| Admin peut attribuer un gestionnaire | `isGestionnaire() = true` apres `attach()` |
| Admin peut retirer un gestionnaire | `isGestionnaire() = false` apres `detach()` |
| Plusieurs gestionnaires sur la meme salle | `gestionnaires()->count() = 2` |
| `isGestionnaire()` false si non assigne | Controle negatif |
| Gestionnaire peut valider sa salle | `can('valider', $resa) = true` + `valider()` OK |
| Gestionnaire ne peut pas valider autre salle | `can('valider', $resa) = false` |
| Gestionnaire peut invalider sa salle | `invalider()` OK |
| Gestionnaire peut supprimer reservation en attente | Suppression OK |
| Gestionnaire peut modifier reservation | `update()` OK |
| Gestionnaire peut modifier sa salle | `can('update', $salle) = true` |
| Gestionnaire ne peut pas modifier autre salle | `can('update', $autreSalle) = false` |
| Admin peut tout valider | `can('valider') = true` quelle que soit la salle |

---

### `ResourcesFormTest.php` — formulaires Filament

Forms `create` et `edit` valides et invalides, dont :

#### SalleResource — lieu et unite (6 tests)

| Test | Ce qui est verifie |
|---|---|
| Creation avec `unite_id` | `unite_id` en base |
| Edition pre-remplit `lieu_unite_id` | `afterStateHydrated()` |
| Changer lieu remet `unite_id` à null | `afterStateUpdated()` |
| Table affiche lieu et unite | Colonnes visibles |
| Filtre par lieu restreint | `whereHas('unite')` |
| Filtre vide retourne tout | Pas de filtre applique |

---

## Setup `beforeEach` global

Chaque test repart d'une base vide (`RefreshDatabase`).

```php
beforeEach(function () {
    // Permissions guard web
    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::admin', 'web');

    // Users
    $this->user  = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::reservation.view');
    $this->admin->givePermissionTo('resa::admin');

    // Structure BDD via seeders du module
    $this->seed(ResaDatabaseSeeder::class);

    // Salle simulateur et ses postes
    $this->salle  = Salle::where('libelle_court', 'Salle 1')->firstOrFail();
    $this->poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
    $this->poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
    $this->poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
    $this->fpv    = TypeCapacite::where('libelle_court', 'FPV')->firstOrFail();
    $this->mm20   = TypeCapacite::where('libelle_court', '20mm')->firstOrFail();

    // Creneaux garantis dans le futur
    $this->debutLibre = now()->addWeek()->startOfWeek()->setTime(9, 0)->toDateTimeString();
    $this->finLibre   = now()->addWeek()->startOfWeek()->setTime(11, 0)->toDateTimeString();
    $this->debutPris  = now()->addWeek()->startOfWeek()->setTime(14, 0)->toDateTimeString();
    $this->finPrise   = now()->addWeek()->startOfWeek()->setTime(16, 0)->toDateTimeString();
});
```

## Setup `beforeEach` — groupe Salle classique

Le groupe `Salle classique` a son propre `beforeEach` imbrique qui ajoute la salle 2 et son poste fictif :

```php
describe('Salle classique', function () {

    beforeEach(function () {
        $this->salleClassique  = Salle::where('libelle_court', 'Salle 2')->firstOrFail();
        $this->posteFictif     = SallePoste::where('salle_id', $this->salleClassique->id)
                                           ->where('libelle_court', 'Salle entiere')
                                           ->firstOrFail();
        $this->typeSalleActive = $this->salleClassique->type_salle_id;

        $this->debutClassique = now()->addWeek()->startOfWeek()->setTime(9, 0)->toDateTimeString();
        $this->finClassique   = now()->addWeek()->startOfWeek()->setTime(11, 0)->toDateTimeString();
    });
```

---

## Helpers

### `getEventsForRole(?User $user, int $salleId)`

Reproduit la logique de `getEvents()` du widget sans passer par FullCalendar.

Les evenements sont charges via AJAX par FullCalendar — ils ne sont pas dans le HTML initial. `assertSee()` ne peut pas les tester. Ce helper reproduit exactement le meme filtre SQL.

```php
// Visiteur — uniquement validation = 1
$events = getEventsForRole(null, $this->salle->id);

// User connecte — validation = 1 + ses propres en attente
$events = getEventsForRole($this->user, $this->salle->id);

// Admin — tout
$events = getEventsForRole($this->admin, $this->salle->id);
```

### `validerCreneau(string $debut, string $fin, ?int $userId = null)`

Reproduit la logique de `validerCreneauPublic()` du widget.

Regles validees :
- Pas le week-end
- Pas dans le passe
- Fin > debut
- Debut entre 08h00 et 16h59
- Fin entre 08h01 et 17h00
- Si `$userId` fourni : verifie qu'il n'a pas deja une reservation qui chevauche

!!! warning "Bug Carbon startOfDay"
    Toujours `$d->copy()->startOfDay()` et non `$d->startOfDay()`.

### `creerReservation(User $actingAs, SallePoste $poste, ...)`

Reproduit la logique de l'action finale du Wizard simulateur.

- Appelle `validerCreneau($debut, $fin, $actingAs->id)` avec le userId
- Appelle `isDisponible()` — retourne `null` si creneau pris
- Cree la reservation avec `validation = 1` si admin, `validation = 0` sinon
- Supporte `$pourUser` pour creer pour un tiers (admin uniquement)

```php
// User standard
$resa = creerReservation(
    actingAs:  $this->user,
    poste:     $this->poste1,
    debut:     $this->debutLibre,
    fin:       $this->finLibre,
    capacites: [$this->fpv->id],
    libelle:   'Ma reservation',
);

// Admin pour un tiers — validation = 1 directe
$resa = creerReservation(
    actingAs:  $this->admin,
    poste:     $this->poste1,
    debut:     $this->debutLibre,
    fin:       $this->finLibre,
    capacites: [$this->fpv->id],
    libelle:   'Resa pour tiers',
    pourUser:  $this->user,
);
```

---

## Pourquoi pas `callAction()` pour les actions footer

Les actions `go_supprimer` et `toggle_validation` sont dans `getResaFooterActions()` appelee via `->extraModalFooterActions()`. Elles ne sont pas enregistrees dans `getHeaderActions()` et ne sont donc pas accessibles via `callAction()`.

Ces actions appellent directement des methodes du model — les tests font de meme :

```php
$resa->delete();       // go_supprimer
$resa->valider($admin); // toggle_validation
$resa->invalider();    // toggle_validation
```

---

## Injecter `salleActive` dans les tests Livewire

Depuis l'ajout de la navigation multi-niveaux, `mount()` appelle `resoudreNavigation()` qui peut afficher la vue de selection au lieu du calendrier. Pour forcer l'affichage du calendrier dans les tests, passez les 3 props :

```php
Livewire::test(CalendrierWidget::class, [
    'salleActive'     => $this->salle->id,
    'etapeNavigation' => 'calendrier',
    'typeSalleActive' => $this->salle->type_salle_id,
])
```

`mount()` detecte `$salleActive !== null` et court-circuite `resoudreNavigation()`.

---

## Permissions

| Permission | Role dans le widget |
|---|---|
| `resa::reservation.view` | Voit toutes les reservations |
| `resa::admin` | Active `$adminMode`, autorise valider/invalider sur toutes les salles |
| `resa::gestionnaire_salle` | Active `$adminMode`, valider/invalider uniquement sur ses salles |

---

## Specifiques au mode classique

### Poste fictif "Salle entiere"

Cree par `SallePosteSeeder` pour chaque salle classique avec `system = true`. Il est :

- Utilise comme `salle_poste_id` sur toutes les reservations classiques
- Exclu de `getResources()` via `where('libelle_court', '!=', 'Salle entiere')`
- Non affiche dans les Wizards
- Non supprimable via l'interface (`isSystem() = true`)

### Verification de disponibilite

En mode classique, la disponibilite se verifie sur le poste fictif — si ce poste est occupe, la salle entiere est occupee :

```php
$posteFictif->isDisponible(new \DateTime($debut), new \DateTime($fin))
```

Un seul occupant a la fois — la capacite affichee dans le formulaire est indicative.

### `capacites_utilisees`

Toujours `[]` en mode classique. Les capacites ne s'appliquent qu'au mode simulateur.

---

## Points d'attention

!!! warning "ReservationSeeder non utilise dans les tests"
    `ResaDatabaseSeeder` appelle `ReservationSeeder` qui utilise `User::first()`. Les reservations sont creees manuellement dans chaque test via `Reservation::create()`.

!!! info "addWeek()->startOfWeek() vs next('Monday')"
    `now()->next('Monday')` peut retourner le lundi courant si on est lundi avant l'heure du test. `addWeek()->startOfWeek()` garantit toujours le lundi de la semaine prochaine.

!!! info "adminMode calcule dans mount()"
    Ne pas passer `adminMode` en parametre — utiliser `actingAs($this->admin)` avec les permissions correctes.
