<?php

use App\Actions\UpdateOrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\URL;

// ── Fix 1 : Confirmation protégée par URL signée ──────────────────────────────

test('confirmation page rejects unsigned URL', function () {
    $order = Order::factory()->create();

    $this->get(route('confirmation', $order))
        ->assertStatus(403);
});

test('confirmation page accepts signed URL', function () {
    $order = Order::factory()->create();
    $order->load('items');

    $signedUrl = URL::signedRoute('confirmation', $order);

    $this->get($signedUrl)->assertStatus(200);
});

test('confirmation page rejects tampered signed URL', function () {
    $order = Order::factory()->create();
    $other = Order::factory()->create();

    $signedUrl = URL::signedRoute('confirmation', $order);
    $tampered = str_replace("/{$order->id}?", "/{$other->id}?", $signedUrl);

    $this->get($tampered)->assertStatus(403);
});

// ── Fix 2 : Numéros de commande cryptographiquement sûrs ─────────────────────

test('order number uses Str::random and is 10 characters long', function () {
    $order1 = Order::factory()->create();
    $order2 = Order::factory()->create();

    expect($order1->order_number)->toStartWith('CMD-')
        ->and($order2->order_number)->toStartWith('CMD-')
        ->and($order1->order_number)->not->toBe($order2->order_number);

    expect(strlen(substr($order1->order_number, 4)))->toBe(10);
});

// ── Fix 3 : Checkout génère bien une URL signée ───────────────────────────────

test('checkout store returns a signed confirmation URL', function () {
    $product = Product::factory()->create(['price' => 5000, 'stock' => 10]);

    $response = $this->postJson(route('checkout.store'), [
        'customer' => [
            'first_name' => 'Amadou',
            'last_name' => 'Diallo',
            'phone' => '771234567',
            'email' => 'amadou@test.com',
        ],
        'delivery' => [
            'zone_id' => 1,
            'address' => 'Dakar, Médina',
        ],
        'payment' => ['method' => 'cash'],
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(200)->assertJsonPath('success', true);

    $redirectUrl = $response->json('redirect_url');

    // L'URL doit contenir une signature
    expect($redirectUrl)->toContain('signature=');

    // L'URL signée doit être valide
    $this->get($redirectUrl)->assertStatus(200);
});

// ── Fix 4 : UpdateOrderStatus exige un admin ─────────────────────────────────

test('UpdateOrderStatus is forbidden for unauthenticated user', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    expect(fn () => (new UpdateOrderStatus)->execute($order, 'confirmed'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('UpdateOrderStatus is forbidden for non-admin user', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($user);

    expect(fn () => (new UpdateOrderStatus)->execute($order, 'confirmed'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('UpdateOrderStatus is allowed for admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($admin);

    (new UpdateOrderStatus)->execute($order, 'confirmed');

    expect($order->fresh()->status)->toBe('confirmed');
});

// ── Fix 5 : Rate limiting sur /checkout ──────────────────────────────────────

test('checkout is rate limited after 5 requests per minute', function () {
    $product = Product::factory()->create(['price' => 5000, 'stock' => 100]);

    $payload = [
        'customer' => ['first_name' => 'Test', 'last_name' => 'User', 'phone' => '771234567'],
        'delivery' => ['zone_id' => 1, 'address' => 'Dakar'],
        'payment' => ['method' => 'cash'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson(route('checkout.store'), $payload)->assertStatus(200);
    }

    $this->postJson(route('checkout.store'), $payload)->assertStatus(429);
});

// ── Fix 6 : Headers de sécurité présents ─────────────────────────────────────

test('security headers are present on all responses', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

// ── Fix 7 : Honeypot rejette les soumissions non vides ────────────────────────

test('contact form rejects honeypot field when filled', function () {
    $this->postJson(route('contact.store'), [
        'name' => 'Bot',
        'email' => 'bot@spam.com',
        'subject' => 'Spam',
        'message' => 'This is spam content here',
        'honeypot' => 'filled-by-bot',
    ])->assertStatus(422);
});
