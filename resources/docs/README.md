# Module Resa

## But du module

Resa gère la **réservation de salles et de matériels** au sein de l'application. Il fournit un calendrier de disponibilité, un circuit de demande/validation, et une gestion des ressources associées à chaque salle (postes de travail, capacités techniques, matériels).

## À quoi ça sert

- Réserver un créneau sur une salle (classique ou simulateur) via un calendrier interactif.
- Réserver du matériel indépendamment d'une salle (ex : matériel embarquable, équipement partagé), classé par catégories.
- Faire valider les réservations par un responsable avant qu'elles ne soient effectives.
- Rattacher les salles à la structure géographique/organisationnelle du module RH (lieu, unité), avec une navigation qui saute automatiquement les étapes inutiles quand il n'y a qu'un seul choix possible.
- Notifier par email le(s) responsable(s) d'une salle à chaque nouvelle réservation.

## Pour qui

| Profil | Ce qu'il peut faire |
|---|---|
| **Utilisateur standard** | Consulte le calendrier (public, même sans connexion), réserve une salle ou du matériel pour lui-même, modifie/supprime ses propres réservations tant qu'elles sont en attente. |
| **Gestionnaire de salle** (`resa::gestionnaire_salle`) | Gère uniquement les salles qui lui sont assignées : modifie la salle, ses postes et capacités, valide/invalide les réservations entrantes sur ses salles. |
| **Admin** (`resa::admin`) | Accès complet : toutes les salles, tout le matériel, toutes les réservations. Peut réserver pour le compte d'un tiers (réservation directement validée, avec traçabilité). |

Voir [`resources/docs/guide-utilisateur.md`](resources/docs/guide-utilisateur.md) pour le pas-à-pas détaillé (création de comptes, permissions, procédures de réservation/validation).

## Salles

Une **Salle** (`Salle`) représente un lieu réservable, de deux types (`TypeSalle`) :

- **Classique** : réservation d'un créneau sur la salle entière (pas de sélection de poste).
- **Simulateur** : réservation via un assistant en plusieurs étapes — choix des capacités nécessaires (ex : FPV, 20mm...), sélection d'un **poste** (`SallePoste`) parmi ceux compatibles et disponibles (triés par score de compatibilité avec les besoins exprimés), puis saisie de l'objet de la réservation.

Chaque salle peut être rattachée à une unité (module RH), avoir une image, une couleur, un ou plusieurs emails de notification, et un ou plusieurs gestionnaires.

Le rattachement suit la hiérarchie géographique du module RH : un **Lieu** (`LieuUnite`, ex : Toulon, Brest) regroupe plusieurs **Unités** (`Unite`). Le lieu et les unités doivent donc être créés dans le module RH *avant* de pouvoir les sélectionner comme rattachement d'une salle dans Resa — c'est l'unité (pas le lieu directement) qui est renseignée sur la salle.

## Matériels

Les **Ressources** (`Ressource`) représentent du matériel réservable indépendamment des salles, organisé en **catégories** (`RessourceCategorie`, ex : couleur/icône pour l'affichage). Chaque ressource peut être rattachée à une unité et activée/désactivée (`actif`). Les réservations de matériel (`RessourceReservation`) suivent le même principe de calendrier que les réservations de salle.

## Contraintes générales

- Réservations possibles du lundi au vendredi, 08h00–17h00, jamais dans le passé.
- Un utilisateur ne peut pas avoir deux créneaux qui se chevauchent.
- Toute réservation créée par un utilisateur standard est **en attente de validation** ; seul un admin réservant pour un tiers crée une réservation directement validée.
