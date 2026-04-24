# Guide de Déploiement — Sacoche Chic sur Hostinger Business

## Prérequis

- Hébergement Hostinger Business avec accès SSH
- PHP 8.2 configuré dans hPanel
- Composer disponible sur le serveur
- Accès phpMyAdmin via hPanel

---

## ÉTAPE 1 — Préparer la base de données en local

### 1.1 Passer de SQLite à MySQL localement

Modifier le fichier `.env` local :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sacoche_chic
DB_USERNAME=root
DB_PASSWORD=ton_mdp_local
```

Lancer les migrations :

```bash
php artisan migrate
```

### 1.2 Exporter la base de données en SQL

```bash
mysqldump -u root -p sacoche_chic > sacoche_chic_export.sql
```

Le fichier `sacoche_chic_export.sql` est prêt à être importé dans phpMyAdmin.

---

## ÉTAPE 2 — Créer la base de données sur Hostinger

1. Aller dans **hPanel → Bases de données → Bases de données MySQL**
2. Créer une nouvelle base de données
3. Créer un utilisateur et lui assigner tous les droits sur la BDD
4. Noter les informations suivantes (elles seront nécessaires plus tard) :

```
Host       : localhost
Database   : u123456789_sacoche       ← nom exact affiché dans hPanel
Username   : u123456789_user          ← utilisateur créé
Password   : ton_mot_de_passe
```

### 2.1 Importer le SQL dans phpMyAdmin

1. **hPanel → Bases de données → phpMyAdmin**
2. Sélectionner la base de données créée
3. Onglet **Importer**
4. Choisir le fichier `sacoche_chic_export.sql`
5. Cliquer **Exécuter**

---

## ÉTAPE 3 — Se connecter au serveur via SSH

Le port SSH de Hostinger est **65002** (pas le port 22 standard).

```bash
ssh u123456789@IP_DU_SERVEUR -p 65002
```

> L'IP et le nom d'utilisateur SSH se trouvent dans **hPanel → Avancé → Accès SSH**.

---

## ÉTAPE 4 — Uploader le projet sur le serveur

### Option A — Upload via SCP (depuis ta machine locale)

Ouvrir un terminal **local** (pas SSH) et exécuter :

```bash
scp -P 65002 -r /c/dev/schic u123456789@IP_DU_SERVEUR:/home/u123456789/sacoche_chic
```

### Option B — Via Git (recommandé si le projet est sur GitHub)

Sur le serveur via SSH :

```bash
cd ~
git clone https://github.com/TON_COMPTE/sacoche-chic.git sacoche_chic
```

---

## ÉTAPE 5 — Lier public_html au dossier public de Laravel

Sur Hostinger, la racine web publique est `public_html/`.
Il faut que ce dossier pointe vers `sacoche_chic/public/`.

```bash
# Supprimer le public_html existant (vide par défaut sur un nouveau compte)
rm -rf ~/public_html

# Créer le lien symbolique
ln -s ~/sacoche_chic/public ~/public_html
```

> Si `public_html` contient déjà des fichiers importants, sauvegarde-les avant de le supprimer.

---

## ÉTAPE 6 — Configurer le fichier .env de production

```bash
cd ~/sacoche_chic

# Copier le fichier d'exemple
cp .env.example .env

# Ouvrir l'éditeur
nano .env
```

Remplir le fichier avec les valeurs de production :

```env
APP_NAME="Sacoche Chic"
APP_ENV=production
APP_KEY=                        # Sera généré à l'étape suivante
APP_DEBUG=false
APP_URL=https://tondomaine.com  # Remplacer par ton vrai domaine

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=u123456789_sacoche
DB_USERNAME=u123456789_user
DB_PASSWORD=ton_mot_de_passe

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stack
LOG_LEVEL=error

MAIL_MAILER=log
```

Sauvegarder : `CTRL+O` puis `Entrée`, quitter : `CTRL+X`

---

## ÉTAPE 7 — Installer les dépendances et finaliser

Exécuter ces commandes **dans l'ordre** via SSH :

```bash
cd ~/sacoche_chic

# Vérifier que PHP est en version 8.2+
php -v

# Installer les dépendances PHP (sans les packages de dev)
composer install --no-dev --optimize-autoloader

# Générer la clé d'application
php artisan key:generate

# Créer le lien symbolique pour le stockage des images
php artisan storage:link

# Mettre en cache la configuration, les routes et les vues (performances)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Donner les bonnes permissions aux dossiers d'écriture
chmod -R 775 storage bootstrap/cache
```

---

## ÉTAPE 8 — Vérifier la version PHP sur hPanel

1. **hPanel → Sites Web → PHP**
2. Sélectionner **PHP 8.2**
3. S'assurer que les extensions suivantes sont activées :
   - `pdo_mysql`
   - `mbstring`
   - `openssl`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`
   - `fileinfo`
   - `gd` ou `imagick`

---

## ÉTAPE 9 — Builder les assets CSS/JS

Si `npm` est disponible sur le serveur :

```bash
cd ~/sacoche_chic
npm install
npm run build
```

Sinon, builder **en local** avant l'upload :

```bash
# En local
npm run build

# Puis uploader uniquement le dossier public/build via SCP
scp -P 65002 -r /c/dev/schic/public/build u123456789@IP:/home/u123456789/sacoche_chic/public/build
```

---

## ÉTAPE 10 — Tester le site

1. Ouvrir `https://tondomaine.com` dans le navigateur
2. Vérifier la boutique front-end
3. Vérifier le panel admin sur `https://tondomaine.com/admin`

En cas d'erreur 500, activer temporairement le debug pour voir le message :

```bash
# Dans .env sur le serveur
APP_DEBUG=true
php artisan config:cache
```

Puis **remettre `APP_DEBUG=false`** une fois le problème résolu.

---

## Commandes utiles pour la maintenance

```bash
# Vider tous les caches
php artisan optimize:clear

# Passer en mode maintenance (affiche une page d'attente aux visiteurs)
php artisan down

# Remettre en ligne
php artisan up

# Voir les logs d'erreur
tail -f ~/sacoche_chic/storage/logs/laravel.log

# Mettre à jour le projet depuis Git
cd ~/sacoche_chic
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

---

## Structure finale sur le serveur

```
/home/u123456789/
├── public_html/          → lien symbolique vers sacoche_chic/public/
└── sacoche_chic/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/           ← accessible via public_html
    │   ├── index.php
    │   ├── build/        ← assets CSS/JS compilés
    │   └── storage/      ← lien vers storage/app/public
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env              ← fichier de config production (ne jamais commiter)
```

---

## Checklist finale avant mise en ligne

- [ ] PHP 8.2 activé dans hPanel
- [ ] Base de données MySQL créée et SQL importé
- [ ] Fichier `.env` configuré avec les bonnes infos de BDD
- [ ] `composer install --no-dev` exécuté
- [ ] `php artisan key:generate` exécuté
- [ ] `php artisan storage:link` exécuté
- [ ] `php artisan config:cache && route:cache && view:cache` exécutés
- [ ] Assets CSS/JS buildés (`npm run build`)
- [ ] `APP_DEBUG=false` dans le `.env`
- [ ] Le site s'ouvre sans erreur
- [ ] L'admin est accessible et fonctionnel
