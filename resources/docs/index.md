# Module Reservation - Cahier des charges

## Presentation

Le module **Resa** permet la reservation de postes simulateur dans des salles equipees. Il est integre au serveur **Poseidon** via Skeletor (Laravel + Filament PHP) et utilise **Guava Calendar** pour l'interface calendrier.

---

## Contexte

La Force d'Action Navale dispose de salles de simulation equipees de postes specialises. Chaque poste propose un ensemble de **capacites** (FPV, 20mm, Bob...). Un utilisateur reserve un creneau en precisant les capacites dont il a besoin - le systeme lui presente les postes compatibles et disponibles, et il choisit lui-meme.

**Situation actuelle :** une seule salle, 4 postes. L'architecture est prevue pour accueillir de nouvelles salles sans modification structurelle.

---

## Acteurs

| Acteur | Role |
|---|---|
| Visiteur non connecte | Consulte le calendrier en lecture seule |
| Utilisateur connecte | Reserve un creneau en choisissant ses capacites puis son poste |
| Admin | Valide, modifie, cree des reservations pour des tiers |

---

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Skeletor : Laravel + Filament PHP |
| Frontend | Livewire JS + Blade |
| Calendrier | Guava Calendar CalendarWidget |
| Serveur | Poseidon |
| Base de donnees | MySQL fanlabdb |

---

## Parcours visiteur

Le visiteur accede au calendrier sans connexion.

- Il voit toutes les reservations validees de la salle
- Les colonnes representent les postes
- Les evenements representent les creneaux occupes
- Il ne peut pas reserver - un bouton Se connecter est affiche

---

## Parcours utilisateur connecte

```mermaid
flowchart TD
    A(["Connexion"]) --> B(["Calendrier de la salle"])
    B --> C["Selectionne un creneau"]
    C --> D["Choisit une ou plusieurs capacites"]
    D --> E["Systeme calcule les postes disponibles"]
    E --> F{"Postes disponibles ?"}
    F -- "non" --> G(["Aucun poste disponible"])
    F -- "oui" --> H["Choisit un poste dans la liste"]
    H --> I["Remplit le formulaire"]
    I --> J{"Creneau libre ?"}
    J -- "non" --> K(["Creneau indisponible"])
    J -- "oui" --> L(["Reservation creee en attente"])

    style A fill:#D3D1C7,stroke:#5F5E5A,color:#2C2C2A
    style B fill:#D3D1C7,stroke:#5F5E5A,color:#2C2C2A
    style C fill:#9FE1CB,stroke:#0F6E56,color:#04342C
    style D fill:#9FE1CB,stroke:#0F6E56,color:#04342C
    style E fill:#9FE1CB,stroke:#0F6E56,color:#04342C
    style F fill:#FAC775,stroke:#854F0B,color:#412402
    style G fill:#F7C1C1,stroke:#A32D2D,color:#501313
    style H fill:#9FE1CB,stroke:#0F6E56,color:#04342C
    style I fill:#9FE1CB,stroke:#0F6E56,color:#04342C
    style J fill:#FAC775,stroke:#854F0B,color:#412402
    style K fill:#F7C1C1,stroke:#A32D2D,color:#501313
    style L fill:#C0DD97,stroke:#3B6D11,color:#173404
```

### Algorithme de selection du poste

| Poste | Capacites | Score | Libre | Affiche |
|---|---|---|---|---|
| Poste 1 | FPV, 20mm | 2/2 | oui | prioritaire |
| Poste 2 | FPV | 1/2 | oui | partiel manque 20mm |
| Poste 3 | 20mm | 1/2 | oui | partiel manque FPV |
| Poste 4 | FPV, 20mm, Bob | 2/2 | non | non affiche |

Les postes avec score 0 ne sont pas proposes. Si aucun poste n'est disponible pour les capacites demandees, un message d'indisponibilite est affiche.

---

## Parcours admin

```mermaid
flowchart TD
    A(["Acces calendrier admin"]) --> B["Toutes les reservations"]
    B --> C{"Action choisie"}
    C -- "valider" --> D["Clic reservation en attente"]
    D --> E["Menu Voir Modifier Valider"]
    E --> F(["Reservation validee"])
    C -- "reserver pour tiers" --> G["Choisit capacites et poste"]
    G --> H["Selectionne un utilisateur"]
    H --> I{"User existe"}
    I -- "non" --> J["Creer via Skeletor"]
    J --> K(["Reservation creee validee"])
    I -- "oui" --> K
    F --> L(["refreshRecords"])
    K --> L

    style A fill:#D3D1C7,stroke:#5F5E5A,color:#2C2C2A
    style B fill:#CECBF6,stroke:#534AB7,color:#26215C
    style C fill:#FAC775,stroke:#854F0B,color:#412402
    style D fill:#F5C4B3,stroke:#993C1D,color:#4A1B0C
    style E fill:#F5C4B3,stroke:#993C1D,color:#4A1B0C
    style F fill:#C0DD97,stroke:#3B6D11,color:#173404
    style G fill:#F5C4B3,stroke:#993C1D,color:#4A1B0C
    style H fill:#F5C4B3,stroke:#993C1D,color:#4A1B0C
    style I fill:#FAC775,stroke:#854F0B,color:#412402
    style J fill:#F7C1C1,stroke:#A32D2D,color:#501313
    style K fill:#C0DD97,stroke:#3B6D11,color:#173404
    style L fill:#9FE1CB,stroke:#0F6E56,color:#04342C
```

### Actions disponibles

| Action | Condition |
|---|---|
| Voir le detail | Toujours |
| Valider | Permission resa::validation_salle + en attente |
| Invalider | Permission resa::validation_salle + validee |
| Modifier | En attente uniquement |
| Supprimer | En attente uniquement |
| Reserver pour un tiers | Permission resa::reservation.view |

---

## Regles metier

!!! info "Lecture publique"
    Le calendrier est visible par tous sans connexion. La connexion est requise uniquement pour reserver.

!!! warning "Disponibilite par capacite"
    Un poste est propose uniquement s'il est libre ET couvre au moins une des capacites demandees. L'utilisateur choisit lui-meme parmi les options disponibles.

!!! success "Admin"
    Une reservation creee par un admin est directement validee avec tracabilite complete validated_by et validated_at.

- Connexion obligatoire pour reserver
- Un user + un poste + un creneau = une reservation
- Pas de chevauchement possible sur un meme poste
- Les postes sont tries par score de capacites couvertes
- L'utilisateur choisit lui-meme le poste parmi les disponibles
- Les capacites choisies sont sauvegardees en JSON sur la reservation
- Reservation user : validation = 0 en attente admin
- Reservation admin : validation = 1 directe
- Drag and drop et resize bloques sur les reservations validees sauf admin
- refreshRecords appele apres chaque action

---

## Livrables

- [x] Migrations type_salles, salles, type_postes, salle_postes, type_capacites, poste_capacites, reservations
- [x] Models Eloquent avec relations et methodes metier
- [x] Seeders de donnees initiales
- [x] CalendrierWidget Guava Calendar
- [ ] Algorithme de scoring des postes disponibles
- [ ] Formulaire de reservation avec selection capacites et postes
- [ ] Vues Blade calendrier selection poste formulaire
- [ ] Ressource Filament gestion salles postes capacites
- [ ] Documentation technique widget

---

## Documents associes

- [Flux de reservation](index.md)
- [Schema base de donnees](base-de-donnees.md)
- [Widget calendrier](widget-calendrier.md)
