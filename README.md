# Laravel Ecommerce Starter

Starter kit e-commerce complet basé sur **Laravel 12 + Livewire 4 + Flux UI**. Prêt à être cloné et adapté pour n'importe quelle boutique en ligne.

## Stack technique

- **Laravel 12** — framework PHP
- **Livewire 4** — composants réactifs sans JavaScript
- **Flux UI Free** — bibliothèque de composants Livewire
- **Tailwind CSS** — styles utilitaires
- **SQLite** (défaut) ou MySQL/PostgreSQL
- **Pest 3** — tests

## Fonctionnalités incluses

### Dashboard Admin (`/admin`)
- **Tableau de bord** — KPIs, graphiques revenus/commandes, stock faible
- **Produits** — CRUD avec images, stock, prix, statut
- **Catégories** — gestion avec images et médiathèque
- **Médiathèque** — gestion des images (upload WebP)
- **Commandes** — liste, détails, mise à jour de statut
- **Promotions** — codes coupon (% ou montant fixe, expiration, quota)
- **Utilisateurs** — gestion des comptes et droits admin
- **Paramètres** — infos boutique, zones de livraison, seuil livraison gratuite

### Boutique Frontend (`/`)
- Page d'accueil, catalogue, fiche produit
- Panier et tunnel de commande
- Suivi de commande
- Validation de coupon

### Authentification
- Login / Register / Reset password (via Laravel Fortify)
- Middleware `admin` pour protéger les routes `/admin`

## Prérequis

- PHP >= 8.2
- Composer
- Node.js >= 18
- SQLite (ou MySQL/PostgreSQL)

## Installation rapide

```bash
# 1. Cloner le dépôt
git clone https://github.com/ton-compte/laravel-ecommerce-starter mon-shop
cd mon-shop

# 2. Dépendances
composer install
npm install && npm run build

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
php artisan migrate --seed

# 5. Lancer le serveur
composer run dev
```

Accès admin : `admin@example.com` / `password`

## Personnalisation pour un nouveau projet

### 1. Nom et branding
```env
# .env
APP_NAME="Ma Boutique"
APP_URL=https://mon-domaine.com
```

### 2. Couleur principale
La couleur `brand-pink` est définie dans `tailwind.config.js`. Change-la selon ton identité visuelle.

### 3. Données de démonstration
Les seeders dans `database/seeders/` contiennent des données d'exemple. Adapte-les à ton secteur :
- `CategorySeeder.php` — tes catégories
- `ProductSeeder.php` — tes produits exemples
- `SettingsSeeder.php` — zones de livraison, nom boutique

### 4. Devise et formats
Recherche `FCFA` dans les vues pour adapter la devise à ton marché.

### 5. Langue et locale
```env
APP_LOCALE=fr
APP_FAKER_LOCALE=fr_FR
```

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/        # ShopController (frontend)
│   └── Middleware/         # IsAdmin
├── Models/                 # Product, Order, Category, Coupon, Media, Setting
└── Livewire/Actions/       # Logout

database/
├── migrations/             # Schéma complet
├── factories/              # Factories pour les tests
└── seeders/                # Données de démo

resources/views/
├── components/admin/       # Composants Livewire admin (inline)
│   ├── ⚡dashboard.blade.php
│   ├── products/⚡index.blade.php
│   ├── orders/⚡index.blade.php
│   └── ...
├── components/shop/        # Vues frontend boutique
└── layouts/                # Layouts app + shop + auth

routes/
├── web.php                 # Routes frontend + admin
└── auth.php                # Routes authentification
```

## Utilisation pour chaque nouveau projet

```bash
# Depuis GitHub — utilise "Use this template"
# ou clone et réinitialise git

git clone https://github.com/ton-compte/laravel-ecommerce-starter mon-nouveau-shop
cd mon-nouveau-shop
rm -rf .git && git init

# Lancer l'assistant de configuration
php artisan app:setup
```

## Tests

```bash
php artisan test --compact
```

## Licence

MIT
