# Déploiement Laravel sur Hostinger

## Prérequis

- Accès SSH au serveur Hostinger
- GitHub CLI (`gh`) installé en local
- Repo GitHub créé

---

## ⚠️ Structure Hostinger — À lire en premier

Sur Hostinger, chaque domaine a **deux dossiers** :

```
~/domains/votre-domaine.com/public_html/   ← Document root (servi par Apache)
~/domains/votre-domaine.com/               ← Dossier projet Laravel (hors web)
```

> **Attention** : Hostinger crée aussi un sous-domaine temporaire du type `darkviolet-lark-284626.hostingersite.com`. Si votre domaine principal pointe vers ce sous-domaine, le bon dossier à utiliser est `~/domains/darkviolet-lark-284626.hostingersite.com/` et non `~/domains/votre-domaine.com/`.

Pour identifier le bon dossier, cherchez où sont les fichiers `artisan` des anciens projets :

```bash
find ~/ -name "artisan" 2>/dev/null
```

Pour confirmer quel dossier est réellement servi par votre domaine :
```bash
curl -s https://votre-domaine.com | head -10
```

---

## 1. Préparer le repo GitHub en local

```bash
# Initialiser git proprement
git init
git add .
git commit -m "Initial commit"

# Créer le repo et pusher
gh repo create babsacademy/nom-projet --public --source=. --remote=origin --push

# Renommer master → main si besoin
git branch -m master main
git push origin -u main
gh repo edit babsacademy/nom-projet --default-branch main
```

### Authentification GitHub (une seule fois par machine)

```bash
gh auth login
# Choisir : GitHub.com → HTTPS → Paste an authentication token
```

Générer un token sur : https://github.com/settings/tokens
Scopes minimum requis : `repo`, `read:org`, `workflow`

---

## 2. Déployer sur Hostinger via SSH

### Étape 1 — Cloner le projet

```bash
cd ~/domains/votre-domaine.com/
git clone https://github.com/babsacademy/nom-projet.git
cd nom-projet
```

### Étape 2 — Installer les dépendances PHP

```bash
composer install --no-dev --optimize-autoloader
```

### Étape 3 — Configurer le `.env`

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

Valeurs importantes à configurer :
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nom_base
DB_USERNAME=utilisateur
DB_PASSWORD=mot_de_passe
```

### Étape 4 — Migrations et seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

> Si un seeder plante, relancez-le individuellement :
> ```bash
> php artisan db:seed --class=NomDuSeeder --force
> ```

### Étape 5 — Builder les assets frontend

```bash
npm install && npm run build
```

### Étape 6 — Copier les fichiers publics dans `public_html`

```bash
cp -r ~/domains/votre-domaine.com/nom-projet/public/. ~/domains/votre-domaine.com/public_html/
```

### Étape 7 — Mettre à jour `public_html/index.php`

```bash
nano ~/domains/votre-domaine.com/public_html/index.php
```

Modifier les deux chemins :
```php
require __DIR__.'/../nom-projet/vendor/autoload.php';
$app = require_once __DIR__.'/../nom-projet/bootstrap/app.php';
```

### Étape 8 — Lien symbolique Storage

```bash
ln -s ~/domains/votre-domaine.com/nom-projet/storage/app/public \
      ~/domains/votre-domaine.com/public_html/storage
```

### Étape 9 — Mettre en cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Étape 10 — Vérification finale

```bash
curl -s https://votre-domaine.com | head -10
```

La réponse doit afficher le HTML de votre app avec le bon `<title>`.

---

## 3. Mettre à jour le site (déploiements suivants)

```bash
cd ~/domains/votre-domaine.com/nom-projet

git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Recopier les assets buildés
cp -r public/build ~/domains/votre-domaine.com/public_html/

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

---

## 4. Dépannage fréquent

| Problème | Cause | Solution |
|---|---|---|
| Site affiche un ancien projet | Mauvais dossier servi | Identifier avec `find ~/ -name artisan` |
| `fake()` non défini dans un seeder | Faker absent en production | Remplacer `fake()->x()` par `rand()` ou valeurs statiques |
| Images n'apparaissent pas | Symlink storage manquant | `ln -s .../storage/app/public .../public_html/storage` |
| Paramètres ne s'enregistrent pas | SettingsSeeder n'a pas tourné | `php artisan db:seed --class=SettingsSeeder --force` |
| `vite: command not found` | Node modules absents | `npm install && npm run build` |
| 403 sur `git push` | Token GitHub sans scope | `gh auth refresh -h github.com -s delete_repo` |
| Page blanche / erreur 500 | Logs Laravel | `tail -50 ~/domains/.../storage/logs/laravel.log` |
