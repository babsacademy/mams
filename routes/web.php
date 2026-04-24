<?php

use App\Http\Controllers\Shop;
use Illuminate\Support\Facades\Route;

/* ── Pages publiques (shop) ─────────────────────────────────────── */
Route::get('/', [Shop\HomeController::class, 'index'])->name('home');
Route::get('/boutique', [Shop\CatalogueController::class, 'index'])->name('catalogue');
Route::get('/produit/{product:slug}', [Shop\ProductController::class, 'show'])->name('products.show');
Route::get('/panier', fn () => view('shop.panier'))->name('panier');
Route::get('/checkout', [Shop\CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [Shop\CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
Route::get('/contact', [Shop\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [Shop\ContactController::class, 'store'])->middleware('throttle:3,1')->name('contact.store');
Route::get('/confirmation/{order}', [Shop\ConfirmationController::class, 'show'])->middleware('signed')->name('confirmation');
Route::get('/suivi', [Shop\TrackingController::class, 'index'])->name('tracking');
Route::get('/api/orders/{orderNumber}/track', [Shop\TrackingController::class, 'track'])->middleware('throttle:10,1')->name('api.orders.track');

/* ── Redirection racine → admin ─────────────────────────────────── */
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

/* ── Back-office (auth) ─────────────────────────────────────────── */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});

/* ── Admin ──────────────────────────────────────────────────────── */
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'admin.dashboard')->name('dashboard');
    Route::livewire('/produits', 'admin.products.index')->name('products.index');
    Route::livewire('/produits/creer', 'admin.products.create-edit')->name('products.create');
    Route::livewire('/produits/{product}/modifier', 'admin.products.create-edit')->name('products.edit');
    Route::livewire('/commandes', 'admin.orders.index')->name('orders.index');
    Route::livewire('/commandes/{order}', 'admin.orders.show')->name('orders.show');
    Route::livewire('/categories', 'admin.categories.index')->name('categories.index');
    Route::livewire('/parametres', 'admin.settings.index')->name('settings.index');

    if (config('features.media')) {
        Route::livewire('/mediatheque', 'admin.media.library')->name('media.library');
    }

    if (config('features.storefront')) {
        Route::livewire('/vitrine', 'admin.storefront.index')->name('storefront.index');
    }

    if (config('features.promotions')) {
        Route::livewire('/promotions', 'admin.promotions.index')->name('promotions.index');
    }

    if (config('features.users')) {
        Route::livewire('/utilisateurs', 'admin.users.index')->name('users.index');
    }

    if (config('features.analytics')) {
        Route::livewire('/rapports', 'admin.reports.index')->name('reports.index');
    }

    if (config('features.notifications')) {
        Route::livewire('/notifications', 'admin.notifications.index')->name('notifications.index');
    }
});

require __DIR__.'/settings.php';
