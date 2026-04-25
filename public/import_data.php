<?php
// One-time import script — DELETE AFTER USE
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Support\Facades\DB;

$json = file_get_contents(__DIR__.'/../backup2.json');
if (!$json) { die("backup2.json not found\n"); }
$data = json_decode($json, true);
if (!$data) { die("Invalid JSON\n"); }

echo "<pre>";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    // Truncate in any order (FK checks off)
    DB::table('order_items')->truncate();
    DB::table('orders')->truncate();
    DB::table('product_media')->truncate();
    DB::table('products')->truncate();
    DB::table('categories')->truncate();
    DB::table('settings')->truncate();
    DB::table('media')->truncate();

    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "Tables truncated OK\n";

    // Import categories
    if (!empty($data['categories'])) {
        DB::table('categories')->insert($data['categories']);
        echo "Categories: " . count($data['categories']) . " inserted\n";
    }

    // Import media
    if (!empty($data['media'])) {
        DB::table('media')->insert($data['media']);
        echo "Media: " . count($data['media']) . " inserted\n";
    }

    // Import settings
    if (!empty($data['settings'])) {
        DB::table('settings')->insert($data['settings']);
        echo "Settings: " . count($data['settings']) . " inserted\n";
    }

    // Import products
    if (!empty($data['products'])) {
        DB::table('products')->insert($data['products']);
        echo "Products: " . count($data['products']) . " inserted\n";
    }

    // Import product_media pivot
    if (!empty($data['product_media'])) {
        DB::table('product_media')->insert($data['product_media']);
        echo "Product_media: " . count($data['product_media']) . " inserted\n";
    }

    // Import orders
    if (!empty($data['orders'])) {
        DB::table('orders')->insert($data['orders']);
        echo "Orders: " . count($data['orders']) . " inserted\n";
    }

    // Import order_items
    if (!empty($data['order_items'])) {
        DB::table('order_items')->insert($data['order_items']);
        echo "Order_items: " . count($data['order_items']) . " inserted\n";
    }

    echo "\nDONE — import successful!\n";
} catch (\Throwable $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
