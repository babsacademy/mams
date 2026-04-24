# Guide UI / UX du Tableau de Bord (Dashboard) - Sacoche Chic

Ce document s'adresse au Web Designer afin de comprendre l'architecture, le fonctionnement et les choix UI du tableau de bord d'administration actuel de la boutique "Sacoche Chic".

## 1. Technologies et Écosystème

*   **Frameworks :** Laravel 12, Livewire 4.
*   **Design System :** Tailwind CSS (utilisation intensive des classes utilitaires).
*   **Composants UI :** Livewire Flux UI (Composants natifs : `<flux:card>`, `<flux:button>`, `<flux:icon>`, etc.).
*   **Iconographie :** Heroicons (intégrés via `<flux:icon>`).
*   **Mode Sombre (Dark Mode) :** Entièrement supporté. La palette `zinc` de Tailwind est utilisée (`zinc-50` à `zinc-900`) au lieu de `gray` pour un rendu plus premium et moderne.

## 2. Structure Générale de la Page

La page est structurée dans un container centré de largeur maximale (`max-w-7xl mx-auto pb-12 px-1`).
Elle se décompose en trois grandes zones verticales :

1.  **L'En-tête (Header)**
2.  **Les Cartes de KPI (Indicateurs clés de performance)**
3.  **La Grille de Contenu (Dernières commandes & Alertes de stock)**

---

## 3. Détails des Sections

### A. L'En-tête (Header)
*   **Titre :** "Tableau de bord" en police extra-grasse (`font-extrabold text-xl`).
*   **Sous-titre :** Message de bienvenue clair avec le nom de l'administrateur en surbrillance.
*   **Call-to-Action (CTA) :** Un bouton primaire "Nouveau produit" aligné à droite sur desktop, et prenant 100% de la largeur sur mobile.

### B. Les 4 Cartes de KPI (Indicateurs clés)
Ces cartes sont présentées dans une grille (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`).
Elles bénéficient de micro-interactions au survol : léger soulèvement (`hover:-translate-y-0.5`) et une accentuation de l'ombre (`hover:shadow-md`).

Elles intègrent de manière subtile, un icône distinct pour chaque catégorie de KPI associé à une couleur de fond/border spécifique :

1.  **Commandes (Bleu)**
    *   **Données :** Total de toutes les commandes.
    *   **Graphique :** Mini "Sparkline" (courbe SVG dynamique générée côté serveur) montrant l'évolution sur les 7 derniers jours.
    *   **Badges :** Indique le nombre de commandes "En attente" (Ambre) et l'évolution en pourcentage "vs hier" (Vert ou Rouge avec flèche de tendance).
2.  **Revenus (Émeraude)**
    *   **Données :** Chiffre d'affaires total (statuts: confirmée, expédiée, livrée) en FCFA. Police très grasse pour l'impact.
    *   **Graphique :** Courbe Sparkline Émeraude.
    *   **Badges :** "Confirmés" et la tendance "% vs mois dernier".
3.  **Produits (Indigo)**
    *   **Données :** Nombre total de produits.
    *   **Badges :** Nombre de produits "Actifs" (point marqueur coloré) et "Inactifs".
4.  **Catégories (Rose)**
    *   **Données :** Total des catégories existantes.
    *   **CTA :** Bouton discret pour "Gérer les catégories".

### C. La Grille de Contenu Inférieure
Présentée sur une grille asymétrique (`lg:grid-cols-3`), elle met en valeur l'opérationnel.

#### 1. Dernières commandes (Zone Principale - 2/3 de l'espace)
*   **Entête de carte :** Titre, icône panier, badge affichant le nombre de récentes commandes récupérées (5), et CTA "Voir toutes".
*   **Lignes de tableau (List Items) :** Mises en forme pour ressembler plus à des notifications/cartes de lignes qu'à un tableau HTML basique.
    *   **Visuels :** Avatar généré dynamiquement à partir des initiales du client.
    *   **Identité :** Nom du client, accompagné d'une zone méta contenant : le numéro de commande en format monospaced, la ville, et le temps écoulé (`diffForHumans`).
    *   **Statut :** Badge de couleur (`flux:badge`) adapté selon le statut (`En attente`=Ambre, `Livrée`=Vert, `Expédiée`=Violet, etc.).
    *   **Montant :** Police lisible avec des numéros de largeur fixe (classe `tabular-nums`).

#### 2. Alerte Stock (Zone Latérale - 1/3 de l'espace)
*   *Comportement dynamique :* S'il y a des alertes, la carte se teinte de couleur ambre avec une bordure visible et l'icône "pulse" (battement de cœur/alerte).
*   **Liste de produits (Stock faible <= 5) :**
    *   Miniature carrée du produit. Au passage de la souris, l'image fait un très léger zoom (`group-hover:scale-105`).
    *   Titre tronqué si trop long pour épargner l'interface.
    *   Indicateur bicolore : Rouge pour une "Rupture" (0), Ambre pour "Stock critique".
    *   Badge indiquant la quantité restante.
*   **Cas "Tout va bien" :** S'il n'y a pas d'alerte, un état vide stylisé s'affiche (icône Verte de coche, message rassurant).

---

## 4. Principes d'UI/UX Récemment Appliqués (Anti-Truncation & Responsive)
*   **Pas de coupure brutale :** Utilisation réfléchie des classes `whitespace-nowrap` sur l'ensemble des badges, montants et textes informatifs prioritaires (empêchant le texte de passer sur deux lignes et de créer un design asymétrique).
*   **Dark Mode Optimal :** Tous les fonds blancs (`bg-white`) deviennent de profonds gris/noirs (`dark:bg-zinc-900`). Le contraste des polices respecte l'accessibilité (`text-zinc-500` devient `dark:text-zinc-400`).
*   **Bordures subtiles :** Remplacement des grilles brutes par des `divide-y divide-zinc-100 dark:divide-zinc-800` pour donner de la légèreté visuelle.

## 5. Comment Intervenir ? (Designer)
*   Puisque cette page n'utilise aucune librairie graphique externe comme Chart.js, les "Sparklines" sont en réalité des `<svg><polyline></svg>` dessinées mathématiquement en PHP et Tailwind. *Rendre les courbes plus douces (bezier)* est possible mais nécessitera de modifier la méthode PHP `sparklinePoints()`.
*   Si de nouvelles vues requièrent des statuts avec d'autres codes couleur, on conserve la nomenclature de configuration centralisée `$statusConfig` définie en haut du fichier `.blade.php`.
*   L'espacement global utilise un espacement large `gap-6` et `gap-5` pour aérer l'interface, très inspiré des standards "Dashboard" modernes (comme Linear ou Vercel).
