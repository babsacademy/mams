<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;

beforeEach(function () {
    Setting::set('shipping_zones', json_encode([
        ['value' => 'dakar', 'label' => 'Dakar Centre', 'price' => 2000],
    ]));
});

function checkoutPayload(array $items, array $overrides = []): array
{
    return array_merge([
        'customer' => [
            'first_name' => 'Fatou',
            'last_name' => 'Diallo',
            'phone' => '771234567',
        ],
        'delivery' => [
            'zone_id' => 1,
            'address' => '12 Rue de la Paix',
        ],
        'payment' => [
            'method' => 'cash',
        ],
        'items' => $items,
    ], $overrides);
}

it('decrements product stock when order is placed', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $this->postJson(route('checkout.store'), checkoutPayload([
        ['product_id' => $product->id, 'quantity' => 3],
    ]))->assertSuccessful();

    expect($product->fresh()->stock)->toBe(7);
});

it('returns 422 when stock is insufficient', function () {
    $product = Product::factory()->create(['stock' => 2]);

    $this->postJson(route('checkout.store'), checkoutPayload([
        ['product_id' => $product->id, 'quantity' => 5],
    ]))->assertStatus(422);

    expect($product->fresh()->stock)->toBe(2);
});

it('allows ordering when stock exactly matches requested quantity', function () {
    $product = Product::factory()->create(['stock' => 3]);

    $this->postJson(route('checkout.store'), checkoutPayload([
        ['product_id' => $product->id, 'quantity' => 3],
    ]))->assertSuccessful();

    expect($product->fresh()->stock)->toBe(0);
});

it('restores stock when order is cancelled via admin show component', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => 'confirmed']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    $user = \App\Models\User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    \Livewire\Livewire::test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(8);
    expect($order->fresh()->status)->toBe('cancelled');
});
