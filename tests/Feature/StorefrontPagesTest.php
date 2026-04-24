<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

test('redesigned storefront pages render successfully', function () {
    $category = Category::factory()->create([
        'name' => 'Signature',
        'slug' => 'signature',
    ]);

    $product = Product::factory()->for($category)->create([
        'name' => 'Mams Signature Unit',
        'slug' => 'mams-signature-unit',
        'is_featured' => true,
        'is_new' => true,
        'stock' => 12,
    ]);

    $order = Order::factory()->create([
        'customer_name' => 'Awa Ndiaye',
        'delivery_address' => 'Dakar Plateau',
        'delivery_notes' => 'Livrer demain',
        'payment_method' => 'wave',
        'subtotal' => $product->price,
        'delivery_fee' => 2000,
        'total' => $product->price + 2000,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'price' => $product->price,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Pourquoi nous')
        ->assertSee('Mams Signature Unit')
        ->assertSee('marquee-segment', false);

    $this->get(route('catalogue'))
        ->assertOk()
        ->assertSee('Notre Collection')
        ->assertSee('Mams Signature Unit');

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Commander via WhatsApp')
        ->assertSee('Mams Signature Unit');

    $this->get(route('panier'))
        ->assertOk()
        ->assertSee('Mon Panier');

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('Recapitulatif');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Contactez-nous');

    $this->get(URL::signedRoute('confirmation', $order))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Awa Ndiaye');
});

test('product page resolves media image paths and keeps the main image contained', function () {
    $category = Category::factory()->create();

    $product = Product::factory()->for($category)->create([
        'name' => 'Detail Deep Wave',
        'slug' => 'detail-deep-wave',
        'length_label' => '30"',
        'color_label' => 'Noir & Miel',
        'image_url' => 'media/images/detail-deep-wave.webp',
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee(asset('storage/media/images/detail-deep-wave.webp'), false)
        ->assertSeeText('Longueur 30"')
        ->assertSeeText('Coloris Noir & Miel')
        ->assertSee('max-h-full max-w-full object-contain object-center transition-all duration-300', false);

    $this->get(route('catalogue'))
        ->assertOk()
        ->assertSeeText('Longueur 30"')
        ->assertSeeText('Noir & Miel');
});

test('product page shows a recognizable thumbnail for video media', function () {
    $category = Category::factory()->create();

    $product = Product::factory()->for($category)->create([
        'name' => 'Video Lace Unit',
        'slug' => 'video-lace-unit',
    ]);

    $video = Media::create([
        'filename' => 'demo.mp4',
        'original_name' => 'demo.mp4',
        'path' => 'media/videos/demo.mp4',
        'disk' => 'public',
        'size' => 2048,
        'type' => 'video',
        'mime_type' => 'video/mp4',
        'duration' => 3,
        'thumbnail_path' => null,
    ]);

    $product->media()->attach($video->id);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('muted playsinline preload="metadata"', false)
        ->assertSeeText('Video');
});

test('home page marquee includes repeated segments for full-width coverage', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('marquee-segment', false)
        ->assertSee('marquee-track', false);
});
