# Tests CalendrierWidget - README

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

# Verbose
./vendor/bin/pest --group=Resa --verbose
```

---

## Structure des tests

Les tests sont organises en 3 groupes correspondant aux 3 roles du cahier des charges.

### Visiteur non connecte (3 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit le widget | Le composant Livewire se rend sans erreur |
| Ne voit pas le bouton de reservation | "Se connecter pour reserver" present, "Nouvelle reservation" absent |
| Voit uniquement les reservations validees | `validation = 1` visible, `validation = 0` masquee |

### Utilisateur connecte (7 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit le bouton nouvelle reservation | Bouton present si connecte |
| Voit les reservations validees et les siennes en attente | Filtre correct - les reservations en attente des autres sont masquees |
| Peut creer une reservation sur un creneau libre | Reservation creee avec `validation = 0` |
| Ne peut pas reserver sur un creneau deja pris | Chevauchement bloque par `isDisponible()` |
| Peut supprimer sa reservation en attente | `isEnAttente() = true` + suppression OK |
| Ne peut pas supprimer une reservation validee | `isEnAttente() = false` donc `canModify = false` |
| Ne peut pas reserver le week-end | `validerCreneau()` retourne une erreur |
| Ne peut pas reserver avant 08h00 | Heure de debut invalide |
| Ne peut pas reserver apres 17h00 | Heure de fin invalide |

### Admin (5 tests)

| Test | Ce qui est verifie |
|---|---|
| Voit toutes les reservations y compris en attente | Pas de filtre sur `validation` |
| Peut valider une reservation en attente | `validation = 1`, `validated_by` renseigne |
| Peut invalider une reservation validee | `validation = 0`, `validated_by = null` |
| Peut creer une reservation pour un autre user | `user_id` different de l'admin, `validation = 1` directe |
| Peut supprimer une reservation validee | Suppression possible meme sur `validation = 1` |

---

## Setup `beforeEach`

Chaque test repart d'une base vide (`RefreshDatabase`).

```php
beforeEach(function () {
    // Permissions guard web creees a la volee
    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::validation_salle',  'web');

    // Users
    $this->user  = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::reservation.view');
    $this->admin->givePermissionTo('resa::validation_salle');

    // Structure BDD via seeders du module
    $this->seed(ResaDatabaseSeeder::class);

    // Recupere les objets via leurs libelles
    $this->salle  = Salle::where('libelle_court', 'Salle 1')->firstOrFail();
    $this->poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
    $this->poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
    $this->poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
    $this->fpv    = TypeCapacite::where('libelle_court', 'FPV')->firstOrFail();
    $this->mm20   = TypeCapacite::where('libelle_court', '20mm')->firstOrFail();

    // Creneaux garantis dans le futur (prochain lundi)
    $this->debutLibre = now()->addWeek()->startOfWeek()->setTime(9, 0)->toDateTimeString();
    $this->finLibre   = now()->addWeek()->startOfWeek()->setTime(11, 0)->toDateTimeString();
    $this->debutPris  = now()->addWeek()->startOfWeek()->setTime(14, 0)->toDateTimeString();
    $this->finPrise   = now()->addWeek()->startOfWeek()->setTime(16, 0)->toDateTimeString();
});
```

### Pourquoi `addWeek()->startOfWeek()` et pas `next('Monday')`

`now()->next('Monday')` peut retourner le lundi courant si on est lundi avant l'heure du test, ce qui peut faire echouer la validation "pas dans le passe". `addWeek()->startOfWeek()` garantit toujours le lundi de la semaine prochaine.

---

## Helpers

### `getEventsForRole(?User $user, int $salleId)`

Reproduit la logique de `getEvents()` du widget sans passer par FullCalendar.

Les evenements du calendrier sont charges via requete AJAX par FullCalendar — ils ne sont pas dans le HTML initial du composant Livewire. `assertSee()` ne peut donc pas les tester. Ce helper reproduit exactement le meme filtre SQL.

```php
// Visiteur — uniquement validation = 1
$events = getEventsForRole(null, $this->salle->id);

// User connecte — validation = 1 + ses propres en attente
$events = getEventsForRole($this->user, $this->salle->id);

// Admin — tout
$events = getEventsForRole($this->admin, $this->salle->id);
```

### `validerCreneau(string $debut, string $fin)`

Reproduit la logique de `validerCreneau()` du widget.

Regles validees :
- Pas de reservation le week-end
- Pas de reservation dans le passe
- Fin strictement superieure au debut
- Debut entre 08h00 et 16h59
- Fin entre 08h01 et 17h00

!!! warning "Bug Carbon startOfDay"
    Utiliser `$d->copy()->startOfDay()` et non `$d->startOfDay()`. La methode `startOfDay()` est mutating — elle modifie l'objet en place et remet l'heure a 00:00, ce qui fausse ensuite la validation horaire.

### `creerReservation(User $actingAs, SallePoste $poste, ...)`

Reproduit la logique de l'action finale du Wizard.

- Appelle `validerCreneau()` — retourne `null` si invalide
- Appelle `isDisponible()` — retourne `null` si creneau pris
- Cree la reservation avec `validation = 1` si admin, `validation = 0` sinon
- Supporte `$pourUser` pour creer pour un tiers (admin uniquement)

```php
// User standard — validation = 0
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

Les actions `go_supprimer` et `toggle_validation` sont dans `getResaFooterActions()` appelee via `->extraModalFooterActions()`. Elles ne sont pas enregistrees dans `getHeaderActions()` et ne sont donc pas accessibles via `callAction()` en test.

Ces actions appellent directement des methodes du model (`$resa->delete()`, `$resa->valider()`, `$resa->invalider()`). Les tests appellent ces memes methodes directement — le comportement teste est identique.

---

## Permissions

Les tests creent les permissions a la volee avec `Permission::findOrCreate(..., 'web')`.

Le guard `web` est explicite pour eviter les conflits si l'application definit plusieurs guards.

| Permission | Role dans le widget |
|---|---|
| `resa::reservation.view` | Active `$adminMode` dans le widget — voit tout |
| `resa::validation_salle` | Autorise les actions valider/invalider |

---

## Points d'attention

!!! warning "ReservationSeeder non utilise dans les tests"
    `ResaDatabaseSeeder` appelle `ReservationSeeder` qui utilise `User::first()`. En tests, ce serait le premier user cree par les factories, ce qui fausserait les compteurs. Les reservations sont creees manuellement dans chaque test via `Reservation::create()`.

!!! info "adminMode calcule dans mount()"
    `adminMode` est calcule automatiquement dans `mount()` via `auth()->user()?->can('resa::reservation.view')`. Ne pas le passer en parametre a `Livewire::test()` — utiliser `actingAs($this->admin)` avec les permissions correctes.

!!! info "Structure BDD via seeders"
    Les seeders `ResaDatabaseSeeder` creent exactement la meme configuration que la prod, garantissant que les tests de scoring des capacites correspondent au comportement reel :
    ```
    Poste 1 : FPV, 20mm
    Poste 2 : FPV
    Poste 3 : 20mm
    Poste 4 : FPV, 20mm, Bob
    ```
