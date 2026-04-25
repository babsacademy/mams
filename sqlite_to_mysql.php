<?php
// Run with: php sqlite_to_mysql.php
// Delete this file after use.

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlitePath = __DIR__.'/database/database.sqlite';

if (! file_exists($sqlitePath)) {
    echo "ERROR: SQLite file not found at $sqlitePath\n";
    exit(1);
}

$pdo = new PDO('sqlite:'.$sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "SQLite connected: $sqlitePath\n";
echo "MySQL connection: " . DB::connection()->getDatabaseName() . "\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');

$tables = [
    'categories',
    'media',
    'settings',
    'products',
    'product_media',
    'orders',
    'order_items',
    'users',
];

foreach ($tables as $table) {
    try {
        $rows = $pdo->query("SELECT * FROM \"$table\"")->fetchAll(PDO::FETCH_ASSOC);
        DB::table($table)->truncate();

        if (! empty($rows)) {
            foreach (array_chunk($rows, 50) as $chunk) {
                DB::table($table)->insert($chunk);
            }
            echo "$table: " . count($rows) . " rows imported\n";
        } else {
            echo "$table: empty (skipped)\n";
        }
    } catch (\Throwable $e) {
        echo "$table: ERROR — " . $e->getMessage() . "\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n=== DONE ===\n";
echo "Products: " . DB::table('products')->count() . "\n";
echo "Media: " . DB::table('media')->count() . "\n";
echo "Categories: " . DB::table('categories')->count() . "\n";
