<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

class AppSetup extends Command
{
    protected $signature = 'app:setup';

    protected $description = 'Assistant de configuration interactif pour initialiser une nouvelle boutique';

    private array $currencies = [
        'XOF' => ['code' => 'XOF', 'symbol' => 'FCFA'],
        'EUR' => ['code' => 'EUR', 'symbol' => '€'],
        'MAD' => ['code' => 'MAD', 'symbol' => 'DH'],
        'USD' => ['code' => 'USD', 'symbol' => '$'],
        'GHS' => ['code' => 'GHS', 'symbol' => '₵'],
    ];

    private array $features = [
        'FEATURE_PROMOTIONS' => 'Promotions / Codes coupon',
        'FEATURE_MEDIA' => 'Médiathèque',
        'FEATURE_STOREFRONT' => 'Vitrine (personnalisation)',
        'FEATURE_USERS' => 'Gestion des utilisateurs',
        'FEATURE_ANALYTICS' => 'Rapports & Analytics',
        'FEATURE_NOTIFICATIONS' => 'Notifications (email + WhatsApp)',
    ];

    public function handle(): void
    {
        $this->newLine();
        $this->line('  <fg=magenta;options=bold>╔══════════════════════════════════════════╗</>');
        $this->line('  <fg=magenta;options=bold>║   Laravel Ecommerce Starter — Setup      ║</>');
        $this->line('  <fg=magenta;options=bold>╚══════════════════════════════════════════╝</>');
        $this->newLine();

        // ── ÉTAPE 1 : Boutique ──────────────────────────────────────────
        $this->line('  <fg=cyan;options=bold>[ 1/4 ] Informations de la boutique</>');
        $this->newLine();

        $appName = $this->ask('  Nom de la boutique', 'Ma Boutique');
        $appUrl = $this->ask('  URL de l\'application', 'http://localhost');
        $locale = $this->choice('  Langue', ['fr', 'en'], 0);

        $currencyChoice = $this->choice(
            '  Devise',
            array_merge(array_keys($this->currencies), ['Autre']),
            0
        );

        if ($currencyChoice === 'Autre') {
            $currencyCode = strtoupper($this->ask('  Code devise (ex: DZD)', 'DZD'));
            $currencySymbol = $this->ask('  Symbole devise (ex: DA)', 'DA');
        } else {
            $currencyCode = $this->currencies[$currencyChoice]['code'];
            $currencySymbol = $this->currencies[$currencyChoice]['symbol'];
        }

        // ── ÉTAPE 2 : Base de données ────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>[ 2/4 ] Base de données</>');
        $this->newLine();

        $dbDriver = $this->choice('  Moteur de base de données', ['SQLite', 'MySQL', 'PostgreSQL'], 0);

        $dbConfig = ['connection' => 'sqlite'];

        if ($dbDriver !== 'SQLite') {
            $connection = $dbDriver === 'MySQL' ? 'mysql' : 'pgsql';
            $defaultPort = $dbDriver === 'MySQL' ? '3306' : '5432';
            $dbHost = $this->ask('  Hôte', '127.0.0.1');
            $dbPort = $this->ask('  Port', $defaultPort);
            $dbDatabase = $this->ask('  Nom de la base de données', 'mon_shop');
            $dbUsername = $this->ask('  Utilisateur', 'root');
            $dbPassword = $this->secret('  Mot de passe (laisser vide si aucun)') ?: '';

            $dbConfig = compact('connection', 'dbHost', 'dbPort', 'dbDatabase', 'dbUsername', 'dbPassword');

            // Tester la connexion
            $this->newLine();
            if (! $this->testDatabaseConnection($dbConfig)) {
                $this->error('  Connexion impossible. Vérifiez vos paramètres et relancez app:setup.');

                return;
            }

            $this->line('  <fg=green>✓ Connexion réussie.</>');
        }

        // ── ÉTAPE 3 : Compte admin ───────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>[ 3/4 ] Compte administrateur</>');
        $this->newLine();

        $adminEmail = $this->ask('  Email', 'admin@example.com');

        $adminPassword = null;
        while (true) {
            $adminPassword = $this->secret('  Mot de passe (min. 12 caractères)');
            if (strlen((string) $adminPassword) >= 12) {
                break;
            }
            $this->error('  Le mot de passe doit contenir au moins 12 caractères.');
        }

        // ── ÉTAPE 4 : Fonctionnalités ────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>[ 4/4 ] Modules à activer</>');
        $this->newLine();

        $enabledFeatures = [];

        foreach ($this->features as $key => $label) {
            $enabledFeatures[$key] = $this->confirm("  Activer : {$label} ?", true);
        }

        // ── Confirmation ─────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>Récapitulatif</>');
        $this->newLine();

        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Boutique',  $appName],
                ['URL',       $appUrl],
                ['Langue',    $locale],
                ['Devise',    "{$currencyCode} ({$currencySymbol})"],
                ['Base de données', $dbDriver],
                ['Admin',     $adminEmail],
                ...array_map(
                    fn ($key, $label) => [$label, $enabledFeatures[$key] ? '✓ Activé' : '✗ Désactivé'],
                    array_keys($enabledFeatures),
                    $this->features
                ),
            ]
        );

        if (! $this->confirm('  Confirmer et lancer la configuration ?', true)) {
            $this->warn('  Configuration annulée.');

            return;
        }

        $this->newLine();

        // ── Exécution ────────────────────────────────────────────────────

        $this->task('Préparation du fichier .env', function () use (
            $appName, $appUrl, $locale,
            $currencyCode, $currencySymbol,
            $dbDriver, $dbConfig, $enabledFeatures
        ) {
            $envPath = base_path('.env');

            if (! file_exists($envPath)) {
                copy(base_path('.env.example'), $envPath);
            }

            $this->writeEnv('APP_NAME', "\"{$appName}\"");
            $this->writeEnv('APP_URL', $appUrl);
            $this->writeEnv('APP_LOCALE', $locale);
            $this->writeEnv('APP_FALLBACK_LOCALE', $locale);
            $this->writeEnv('APP_FAKER_LOCALE', $locale === 'fr' ? 'fr_FR' : 'en_US');
            $this->writeEnv('APP_CURRENCY', $currencyCode);
            $this->writeEnv('APP_CURRENCY_SYMBOL', $currencySymbol);

            if ($dbDriver === 'SQLite') {
                $this->writeEnv('DB_CONNECTION', 'sqlite');
            } else {
                $this->writeEnv('DB_CONNECTION', $dbConfig['connection']);
                $this->writeEnv('DB_HOST', $dbConfig['dbHost']);
                $this->writeEnv('DB_PORT', $dbConfig['dbPort']);
                $this->writeEnv('DB_DATABASE', $dbConfig['dbDatabase']);
                $this->writeEnv('DB_USERNAME', $dbConfig['dbUsername']);
                $this->writeEnv('DB_PASSWORD', $dbConfig['dbPassword']);
            }

            foreach ($enabledFeatures as $key => $enabled) {
                $this->writeEnv($key, $enabled ? 'true' : 'false');
            }
        });

        $this->task('Génération de la clé applicative', function () {
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }
        });

        if ($dbDriver === 'SQLite') {
            $this->task('Création de la base SQLite', function () {
                $path = database_path('database.sqlite');
                if (! file_exists($path)) {
                    touch($path);
                }
            });
        }

        $this->task('Migration de la base de données', function () {
            Artisan::call('migrate:fresh', ['--force' => true]);
        });

        $this->task('Chargement des données de démonstration', function () {
            Artisan::call('db:seed', ['--force' => true]);
        });

        $this->task('Création du compte administrateur', function () use ($adminEmail, $adminPassword, $appName) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => Hash::make($adminPassword),
                    'is_admin' => true,
                ]
            );

            Setting::set('site_name', $appName, 'general');
        });

        $this->task('Nettoyage du cache', function () {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        });

        // ── Résumé final ──────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=green;options=bold>✓ Configuration terminée !</>');
        $this->newLine();
        $this->line("  Boutique  : <options=bold>{$appName}</>");
        $this->line("  URL       : <options=bold>{$appUrl}</>");
        $this->line("  Devise    : <options=bold>{$currencyCode} ({$currencySymbol})</>");
        $this->line("  Admin     : <options=bold>{$adminEmail}</>");
        $this->line("  Dashboard : <options=bold>{$appUrl}/admin</>");
        $this->newLine();

        $activeModules = array_keys(array_filter($enabledFeatures));
        $this->line('  Modules actifs : <fg=green>'.implode('</> · <fg=green>', array_map(
            fn ($key) => $this->features[$key],
            $activeModules
        )).'</>');
        $this->newLine();
    }

    private function writeEnv(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        if (preg_match("/^{$key}=/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }

        file_put_contents($envPath, $content);
    }

    private function testDatabaseConnection(array $config): bool
    {
        try {
            $dsn = match ($config['connection']) {
                'mysql' => "mysql:host={$config['dbHost']};port={$config['dbPort']};dbname={$config['dbDatabase']}",
                'pgsql' => "pgsql:host={$config['dbHost']};port={$config['dbPort']};dbname={$config['dbDatabase']}",
            };

            new PDO($dsn, $config['dbUsername'], $config['dbPassword'], [
                PDO::ATTR_TIMEOUT => 5,
            ]);

            return true;
        } catch (PDOException $e) {
            $this->error("  Erreur : {$e->getMessage()}");

            return false;
        }
    }
}
