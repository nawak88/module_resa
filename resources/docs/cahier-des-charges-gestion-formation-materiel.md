# Cahier des charges — Gestion (salles/formations) & Matériel

Document de suivi des évolutions apportées au module Resa en octobre 2026, et des demandes
restantes à traiter. À mettre à jour à chaque nouvelle évolution.

---

## 1. Ce qui a été fait

### 1.1 Correctif calendrier — décalage horaire

- **Problème** : une réservation créée 12h–13h s'affichait 14h–15h dans le calendrier.
- **Cause** : `app.timezone = UTC` alors que les heures saisies sont des heures murales
  locales (Europe/Paris) ; le calendrier (Guava/EventCalendar) reconvertissait ensuite côté
  navigateur, décalant l'affichage.
- **Correctif** : `Reservation::toCalendarEvent()` retague explicitement `debut`/`fin` en
  `Europe/Paris` avant de les transmettre au `CalendarEvent`.

### 1.2 Gestionnaire de salle

- Champ **Gestionnaires** (multi-select) ajouté au formulaire `SalleResource` (salles
  classiques et simulateur).
- `CreateSalle`/`EditSalle` : attribution automatique de la permission
  `resa::gestionnaire_salle` aux gestionnaires choisis, **révocation symétrique** quand un
  gestionnaire est retiré et ne gère plus aucune autre salle.
- `CalendrierWidget` : un gestionnaire de salle (non admin) ne voit que les salles qu'il gère.

### 1.3 Gestionnaire de formation (type de capacité)

- Le champ "Gestionnaires" existait déjà sur `TypeCapaciteForm` (Formation), mais
  **aucune permission n'était réellement posée** : ajouté dans `CreateTypeCapacite` /
  `EditTypeCapacite`, même logique d'attribution/révocation symétrique que pour les salles.
- `Formation::isGestionnaire()` ajouté (miroir de `Salle::isGestionnaire()`).
- `ReservationPolicy::valider()` autorise désormais : admin **ou** gestionnaire de la salle
  concernée **ou** gestionnaire d'une des formations utilisées par la réservation.
- `CalendrierWidget` : nouveau mode **`modeFormationGlobale`** — calendrier transversal
  (toutes salles), filtré par formation via `filtreFormations`, sans navigation
  lieu/unité/salle. Les colonnes (postes) sont calculées dynamiquement à partir des salles
  ayant une réservation pour la formation filtrée, avec préfixe "Salle — Poste" si plusieurs
  salles sont concernées.

### 1.4 Dashboard du gestionnaire de formation

- **`SuiviFormationsWidget`** (existait mais était désactivé dans le panel — réactivé) :
  - Cartes stats (formations gérées, réservations à venir, en attente, ce mois).
  - Onglets par formation si le gestionnaire en gère plusieurs.
  - Calendrier `CalendrierWidget` (mode transversal) embarqué.
  - Tableau détaillé avec filtres (période/statut/stagiaire) et bouton **Valider/Invalider**.
  - Bouton **Voir** (`VoirStagiaireAction`) : modale avec détail de la réservation + infos
    RH du/des stagiaire(s) (grade, spécialité, unité, matricule) via `Marin::fromUser()`.
  - Après (in)validation : `calendarVersion` incrémenté pour forcer le remontage complet du
    calendrier embarqué (plus fiable que l'évènement JS `calendar--refresh` seul, qui dépend
    du chargement différé `x-load`).
- **`ReservationWidget`** : l'assistant générique "Bienvenue sur Resa, configurez votre
  réservation" est masqué pour un gestionnaire de formation **pur** (ni admin, ni
  gestionnaire de salle) — il atterrit directement sur son dashboard dédié.
- **`MesReservationsWidget`** (nouveau) : visible pour tout utilisateur connecté, liste
  **toutes ses réservations** (salles + matériel confondus), avec stats, filtres et
  suppression (uniquement sur ses réservations en attente).

### 1.5 Cohérence du menu "Gestion"

- `SalleResource` : colonne **Type de salle** + filtre ajoutés dans le tableau.
- Réordonnancement des `navigationSort` (les valeurs se chevauchaient) :
  Réservations(1) → Réservations matériels(2) → Salles(3) → Types de salle(4) → Postes(5)
  → Types de poste(6) → Type de capacité(7).

### 1.6 Email de notification — Salle

- Champ **Email(s) de notification** ajouté à `SalleResource`, **visible uniquement en mode
  salle classique** (masqué pour les salles simulateur). Le mécanisme d'envoi existait déjà
  côté backend (`ReservationObserver`), seul le champ UI manquait.

### 1.7 Disponibilité du matériel (point 3 de la demande ci-dessous)

- Nouvelle colonne `statut` sur `Ressource` (`disponible` par défaut / `entretien` /
  `indisponible`), distincte de `actif` (actif = toujours au catalogue).
- `RessourceResource` : champ + colonne + filtre "Disponibilité".
- `RessourceGanttWidget` : le matériel en entretien/indisponible reste **visible** dans le
  planning (libellé de colonne suffixé ⚠️/⛔) mais toute tentative de réservation est
  **bloquée** avec message explicite.

### 1.8 Outillage et infra

- `Modules/Resa/tests/Feature/GestionFormationTest.php` : 54 tests Pest couvrant l'ensemble
  des points 1.2 à 1.7 (non régressé par rapport aux 71 échecs pré-existants côté
  `ResourcesFormTest.php`/`ResourcesAdminTest.php`, dûs à un bug d'infrastructure
  `Livewire::test()->fillForm()` indépendant de ces changements).
- `Modules/Resa/tools/video/` : scripts Playwright pour générer des vidéos de présentation
  (utilisateur / gestion / super-admin) — non exécutés, à vérifier/ajuster en live.
- Mirroring GitHub → GitLab (CI/CD) mis en place pour `skeletor` + les 5 modules
  (`.gitlab-ci.yml` / `.gitlabju-ci.yml` pour Skeletor spécifiquement, car son
  `.gitlab-ci.yml` d'origine est réservé à l'instance GitLab interne/SonarQube).

---

## 2. Demande du 22/10/2026 — Matériel (4 points)

Retours reçus pour le module Resa, concernant la réservation de matériel.

### Point 1 — Type de réservation "Matériel" visible dans les options
> Cibles SEMA, drones aériens (MP Chauvin à valider).

**Statut : largement déjà supporté techniquement** — le sélecteur de réservation propose
déjà "📦 Ressources matérielles" dès qu'une `Ressource` existe pour l'unité (via
`RessourceGanttWidget`, `Ressource` + `RessourceCategorie`). **Reste à faire : configuration**
(créer les catégories "Cibles SEMA", "Drones aériens" et les ressources correspondantes),
sauf si une UX différente est souhaitée (onglet dédié plutôt que noyé dans le sélecteur
générique — à clarifier).

### Point 2 — Champs obligatoires à la réservation de matériel
> Nom du bâtiment, service concerné, nom+grade de l'emprunteur, nom+grade de l'officier
> responsable.

**Statut : fait.**
- Migration `RessourceReservation` : `batiment`, `service` (texte libre), `emprunteur_user_id`
  et `officier_responsable_user_id` (FK `users`, nullable en base pour ne pas casser les
  réservations existantes).
- Les grades ne sont **pas stockés en dur** : résolus à l'affichage via
  `RessourceReservation::emprunteur_grade` / `officier_responsable_grade`
  (accessors qui appellent `Marin::fromUser()`), pour rester synchronisés si le grade change
  en base RH après la réservation.
- `RessourceGanttWidget::getNouvelleResaForm()` : 4 champs requis (bâtiment en texte libre,
  service en texte libre, emprunteur et officier en `Select` recherchable sur les
  utilisateurs). Obligatoires côté formulaire uniquement (pas de contrainte `NOT NULL` en
  base, pour ne pas bloquer d'éventuelles réservations créées par ailleurs).
- `getVoirResaForm()` : affiche bâtiment/service/emprunteur+grade/officier+grade dans le
  détail de la réservation.
- 3 tests Pest dédiés.

### Point 3 — Gestion de la disponibilité du matériel
**Statut : fait** (voir section 1.7 ci-dessus).

### Point 4 — Validation des réservations et droits "semi-admin"
> Validation/refus autonome. Semi-admin : modifier des noms, ajouter/retirer du matériel.

**Statut : fait pour le cas (a)** — validation/refus autonome par catégorie de matériel.
- `RessourceCategorie::gestionnaires()` + `isGestionnaire()` (même pattern que
  `Salle`/`Formation`).
- Permission `resa::gestionnaire_ressource`, attribuée/révoquée depuis
  `CreateRessourceCategorie`/`EditRessourceCategorie` (révocation symétrique : seulement si
  l'utilisateur ne gère plus aucune autre catégorie).
- Nouvelle **`RessourceReservationPolicy::valider()`** (admin, ou gestionnaire de la
  catégorie de la ressource réservée) — enregistrée dans `ResaServiceProvider`.
- `RessourceGanttWidget` : le bouton Valider/Invalider utilise désormais cette policy (plus
  seulement `adminMode`/admin complet) ; les réservations en attente du matériel d'une
  catégorie gérée sont visibles même si elles ne sont pas de l'utilisateur ; une réservation
  créée par un gestionnaire sur sa propre catégorie est **validée directement** (comme pour
  salle/formation).
- 8 tests Pest dédiés.

**Reste en attente de clarification — cas (b)** : le rôle "semi-admin" qui "modifie les
noms des **utilisateurs**" évoqué dans la demande initiale irait au-delà de la gestion du
matériel (toucherait aux fiches utilisateurs elles-mêmes, hors périmètre Resa, module
RH/comptes). **Non traité** — à valider avec le demandeur si ce besoin est toujours
d'actualité en plus du cas (a) déjà couvert.

---

## 3. Prochaines étapes proposées

1. Clarifier le point 4 cas (b) (droit d'édition sur les fiches utilisateurs — toujours
   demandé en plus du cas (a) déjà couvert ?).
2. Configurer les catégories/ressources SEMA et drones (point 1), une fois le périmètre UX
   confirmé.
3. Tester en conditions réelles l'outillage vidéo (`tools/video`) et publier les vidéos de
   présentation.
