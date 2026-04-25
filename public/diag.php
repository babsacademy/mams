<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "<pre>";

// DB counts
echo "=== DATABASE ===\n";
foreach (['categories','products','media','product_media','settings','orders','order_items'] as $t) {
    try { echo "$t: " . DB::table($t)->count() . "\n"; } catch(\Exception $e) { echo "$t: ERROR - " . $e->getMessage() . "\n"; }
}

// Products detail
echo "\n=== PRODUCTS ===\n";
$products = DB::table('products')->get();
foreach ($products as $p) {
    echo "ID:{$p->id} | {$p->name} | price:{$p->price}\n";
}

// Media detail
echo "\n=== MEDIA (first 10) ===\n";
$media = DB::table('media')->limit(10)->get();
foreach ($media as $m) {
    $exists = Storage::disk('public')->exists($m->path) ? 'FILE_OK' : 'MISSING';
    echo "ID:{$m->id} | path:{$m->path} | $exists\n";
}

// product_media links
echo "\n=== PRODUCT_MEDIA LINKS ===\n";
$links = DB::table('product_media')->get();
foreach ($links as $l) {
    echo "product_id:{$l->product_id} -> media_id:{$l->media_id}\n";
}

// Storage files
echo "\n=== FILES IN storage/media/ ===\n";
$files = Storage::disk('public')->files('media');
foreach ($files as $f) { echo "$f\n"; }
$filesImages = Storage::disk('public')->files('media/images');
foreach ($filesImages as $f) { echo "$f\n"; }
$filesVideos = Storage::disk('public')->files('media/videos');
foreach ($filesVideos as $f) { echo "$f\n"; }

// Symlink check
echo "\n=== SYMLINK ===\n";
$link = __DIR__.'/storage';
if (is_link($link)) { echo "storage symlink OK -> " . readlink($link) . "\n"; }
elseif (is_dir($link)) { echo "storage is a directory (not symlink)\n"; }
else { echo "storage symlink MISSING\n"; }

echo "</pre>";
