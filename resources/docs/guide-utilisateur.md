# Guide utilisateur — Module Réservation

## Accès au module

Le module Resa est accessible depuis le panneau **Resa** de l'application. L'URL dépend de la configuration du serveur.

Le calendrier est visible par tous les visiteurs, même non connectés. La connexion est nécessaire pour réserver.

---

## Créer un compte Admin

Un admin a accès complet au module : toutes les salles, toutes les réservations, validation sans restriction.

**Étapes :**

1. Créer l'utilisateur via le panneau d'administration principal (ou `User::factory()->create()` en dev)
2. Attribuer les permissions via l'interface Spatie ou en console :

```php
$user = User::find($id);
$user->givePermissionTo('resa::admin');
$user->givePermissionTo('resa::reservation.view');
```

Ou via le rôle complet qui regroupe toutes les permissions :

```php
$user->assignRole('resa::admin');
```

**Lancer le seeder de permissions** (première installation) :

```bash
php artisan db:seed --class="Modules\Resa\Database\Seeders\PermissionsSeeder"
php artisan db:seed --class="Modules\Resa\Database\Seeders\RolesSeeder"
```

---

## Créer un compte Gestionnaire de salle

Un gestionnaire peut gérer uniquement les salles qui lui sont assignées : modifier la salle, créer des postes et capacités, valider les réservations entrantes.

**Depuis l'interface (recommandé) :**

1. Se connecter avec un compte admin
2. Aller dans **Resa > Gestion > Salles**
3. Ouvrir la salle concernée → **Modifier**
4. Champ **Gestionnaires** → sélectionner l'utilisateur
5. Sauvegarder

La permission `resa::gestionnaire_salle` est accordée automatiquement à l'utilisateur lors de la sauvegarde.

**Ou en console :**

```php
use Spatie\Permission\Models\Permission;

$salle = \Modules\Resa\Models\Salle::find($salleId);
$salle->gestionnaires()->attach($userId);

// Accorder la permission manuellement si bypass de l'interface
$user->givePermissionTo(Permission::firstOrCreate(['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']));
```

Un gestionnaire peut être assigné à plusieurs salles. Une salle peut avoir plusieurs gestionnaires.

---

## Configurer l'email de notification d'une salle

Quand une réservation est créée sur une salle, un email de notification est envoyé automatiquement si le champ email est renseigné.

1. Aller dans **Resa > Gestion > Salles** → **Modifier**
2. Champ **Email(s) de notification** → saisir l'adresse
3. Pour plusieurs destinataires : `responsable@base.fr, chef@unite.fr`

Si le champ est vide ou null, aucun email n'est envoyé.

---

## Réserver une salle (utilisateur)

### Mode Simulateur

1. Ouvrir le calendrier de la salle
2. Cliquer sur un créneau libre ou sélectionner une plage horaire
3. **Étape 1** — choisir les capacités nécessaires (FPV, 20mm...) et valider les horaires
4. **Étape 2** — choisir un poste parmi ceux disponibles (triés par score de compatibilité)
5. **Étape 3** — renseigner un libellé (objet de la réservation)
6. Confirmer → réservation créée **en attente de validation**

### Mode Classique

1. Ouvrir le calendrier de la salle
2. Cliquer sur un créneau libre
3. **Étape 1** — vérifier les horaires (la salle est libre ou bloquée)
4. **Étape 2** — renseigner un libellé
5. Confirmer → réservation créée **en attente de validation**

**Contraintes horaires :** du lundi au vendredi, entre 08h00 et 17h00. Pas de réservation dans le passé. Un utilisateur ne peut pas avoir deux créneaux qui se chevauchent.

---

## Valider une réservation (admin ou gestionnaire)

1. Ouvrir le calendrier — les réservations en attente apparaissent en gris
2. Cliquer sur la réservation
3. Dans le modal → bouton **Valider**
4. La réservation passe en vert

Un admin voit et peut valider toutes les réservations de toutes les salles.  
Un gestionnaire voit uniquement les réservations des salles qui lui sont assignées.

---

## Invalider une réservation

Même procédure que la validation — le bouton devient **Invalider** si la réservation est déjà validée. La réservation repasse en attente.

---

## Modifier une réservation

**Via le calendrier :**
- Glisser-déposer pour changer le créneau (admin et gestionnaire uniquement)
- Redimensionner pour modifier la durée

**Via la liste :**
1. Aller dans **Resa > Réservations**
2. Cliquer sur **Modifier** sur la ligne concernée

Un utilisateur standard peut modifier uniquement ses propres réservations en attente. Une réservation validée ne peut plus être modifiée par l'utilisateur (seulement par admin/gestionnaire).

---

## Supprimer une réservation

- **Utilisateur** : peut supprimer ses réservations en attente uniquement
- **Admin / Gestionnaire** : peut supprimer n'importe quelle réservation de leurs salles

Dans le calendrier : cliquer sur la réservation → bouton **Supprimer** dans le modal.

---

## Réserver pour un tiers (admin uniquement)

1. Ouvrir le calendrier
2. Sélectionner un créneau → lancer le Wizard
3. **Étape 3** — champ **Utilisateur** → sélectionner le bénéficiaire
4. Confirmer → réservation directement **validée** avec tracabilité (`validated_by`, `validated_at`)

---

## Configurer une salle (admin ou gestionnaire)

### Créer une salle

Accessible uniquement aux admins via **Resa > Gestion > Salles > Créer**.

| Champ | Description |
|-------|-------------|
| Type de salle | Simulateur ou Classique |
| Lieu / Unité | Rattachement géographique (module RH) |
| Code | Identifiant court |
| Titre | Nom affiché dans le calendrier |
| Capacité | Nombre de personnes (indicatif) |
| Image | Photo de la salle |
| Email(s) | Destinataires des notifications |
| Gestionnaires | Utilisateurs autorisés à gérer cette salle |

### Créer un poste

1. **Resa > Gestion > Salles** → ouvrir la salle → onglet **Postes**
2. **Créer un poste** → renseigner le libellé et l'ordre
3. Assigner des capacités depuis l'onglet **Capacités du poste**

### Ajouter des capacités

1. **Resa > Gestion > Types de capacité** → créer les types (FPV, 20mm...)
2. Revenir sur le poste → onglet **Capacités** → lier les types

---

## Navigation géographique

Si les salles sont rattachées à des lieux et unités (via le module RH), le calendrier affiche automatiquement les étapes de sélection :

- **Plusieurs lieux** → choix du lieu
- **1 seul lieu** → saute directement aux unités
- **1 seule unité** → saute directement aux salles
- **1 seule salle** → ouvre directement le calendrier

Cette navigation est transparente pour l'utilisateur — les étapes inutiles sont automatiquement sautées.

---

## Permissions disponibles

| Permission | Description |
|-----------|-------------|
| `resa::admin` | Accès complet — toutes les salles et réservations |
| `resa::reservation.view` | Voir toutes les réservations dans la liste Resource |
| `resa::gestionnaire_salle` | Gérer et valider uniquement les salles assignées |
