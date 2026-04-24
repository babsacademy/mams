<?php

use App\Models\Coupon;
use App\Models\Setting;

beforeEach(function () {
    Setting::set('shipping_zones', json_encode([
        ['value' => 'dakar', 'label' => 'Dakar Centre', 'price' => 2000],
    ]));
});

// ── Coupon::isValid() ────────────────────────────────────────────────────────

it('validates an active coupon with no restrictions', function () {
    $coupon = Coupon::factory()->create(['is_active' => true, 'type' => 'percent', 'value' => 10]);

    expect($coupon->isValid(50000))->toBeTrue();
});

it('rejects an inactive coupon', function () {
    $coupon = Coupon::factory()->create(['is_active' => false]);

    expect($coupon->isValid(50000))->toBeFalse();
});

it('rejects an expired coupon', function () {
    $coupon = Coupon::factory()->create(['expires_at' => now()->subDay()]);

    expect($coupon->isValid(50000))->toBeFalse();
});

it('rejects a coupon that exceeded max uses', function () {
    $coupon = Coupon::factory()->create(['max_uses' => 5, 'uses_count' => 5]);

    expect($coupon->isValid(50000))->toBeFalse();
});

it('rejects a coupon when order total is below min_order', function () {
    $coupon = Coupon::factory()->create(['min_order' => 20000]);

    expect($coupon->isValid(10000))->toBeFalse();
});

// ── Coupon::discountFor() ────────────────────────────────────────────────────

it('calculates percent discount correctly', function () {
    $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20]);

    expect($coupon->discountFor(50000))->toBe(10000.0);
});

it('calculates fixed discount correctly', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 5000]);

    expect($coupon->discountFor(50000))->toBe(5000.0);
});

it('caps fixed discount at order total', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 100000]);

    expect($coupon->discountFor(30000))->toBe(30000.0);
});

// ── POST /coupon/valider ─────────────────────────────────────────────────────

it('validates a valid coupon via the API', function () {
    Coupon::factory()->create([
        'code' => 'PROMO20',
        'type' => 'percent',
        'value' => 20,
        'is_active' => true,
    ]);

    $this->postJson(route('coupon.validate'), ['code' => 'promo20', 'total' => 50000])
        ->assertSuccessful()
        ->assertJsonStructure(['discount', 'label'])
        ->assertJsonFragment(['discount' => 10000.0]);
})->skip('Route coupon.validate not yet implemented.');

it('rejects an unknown coupon code', function () {
    $this->postJson(route('coupon.validate'), ['code' => 'UNKNOWN', 'total' => 50000])
        ->assertStatus(422)
        ->assertJsonFragment(['error' => 'Code invalide ou expiré']);
})->skip('Route coupon.validate not yet implemented.');

it('rejects an expired coupon via the API', function () {
    Coupon::factory()->create(['code' => 'EXPIRED', 'expires_at' => now()->subDay(), 'is_active' => true]);

    $this->postJson(route('coupon.validate'), ['code' => 'EXPIRED', 'total' => 50000])
        ->assertStatus(422);
})->skip('Route coupon.validate not yet implemented.');

// ── storeOrder() avec coupon ─────────────────────────────────────────────────

it('applies a coupon discount when placing an order', function () {
    Coupon::factory()->create([
        'code' => 'SAVE5000',
        'type' => 'fixed',
        'value' => 5000,
        'is_active' => true,
    ]);

    $this->postJson(route('order.store'), [
        'firstname' => 'Awa',
        'lastname' => 'Sow',
        'tel' => '771234567',
        'address' => 'VDN',
        'city' => 'dakar',
        'payment' => 'cod',
        'coupon_code' => 'SAVE5000',
        'discount_amount' => 5000,
        'items' => [
            ['id' => null, 'name' => 'Sac Prestige', 'price' => 45000, 'quantity' => 1],
        ],
    ])->assertSuccessful()
        ->assertJsonFragment(['discount' => 5000.0]);

    $coupon = Coupon::where('code', 'SAVE5000')->first();
    expect($coupon->uses_count)->toBe(1);

    $order = \App\Models\Order::first();
    expect($order->coupon_code)->toBe('SAVE5000')
        ->and((float) $order->discount_amount)->toBe(5000.0)
        ->and((float) $order->total)->toBe(42000.0); // 45000 + 2000 livraison - 5000
})->skip('Coupon integration with checkout not yet implemented.');

it('increments coupon uses_count after successful order', function () {
    Coupon::factory()->create([
        'code' => 'USES',
        'type' => 'percent',
        'value' => 10,
        'is_active' => true,
        'uses_count' => 0,
    ]);

    $this->postJson(route('order.store'), [
        'firstname' => 'Marie',
        'lastname' => 'Fall',
        'tel' => '771234567',
        'address' => 'HLM',
        'city' => 'dakar',
        'payment' => 'cod',
        'coupon_code' => 'USES',
        'discount_amount' => 0,
        'items' => [
            ['id' => null, 'name' => 'Pochette', 'price' => 20000, 'quantity' => 1],
        ],
    ])->assertSuccessful();

    expect(Coupon::where('code', 'USES')->first()->uses_count)->toBe(1);
})->skip('Coupon integration with checkout not yet implemented.');
