<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

// ── Stock restoration via orders/index component (the bug fix) ────────────────

test('cancelling order via index component restores product stock', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => 'confirmed']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 2,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.index')
        ->call('updateStatus', $order->id, 'cancelled');

    expect($product->fresh()->stock)->toBe(7)
        ->and($order->fresh()->status)->toBe('cancelled');
});

test('cancelling order via show component restores product stock', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => 'confirmed']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(8)
        ->and($order->fresh()->status)->toBe('cancelled');
});

// ── Idempotency: double cancel does not double-restore stock ──────────────────

test('cancelling an already-cancelled order does not restore stock again', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => 'cancelled']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    // Stock must remain at 5, not become 8
    expect($product->fresh()->stock)->toBe(5);
});

test('calling updateStatus twice with same status is idempotent', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order = Order::factory()->create(['status' => 'pending']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 2,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus')
        ->set('status', 'cancelled')
        ->call('updateStatus');

    // Stock should be 12 (10 + 2), not 14 (10 + 2 + 2)
    expect($product->fresh()->stock)->toBe(12);
});

// ── Non-cancellation transitions do not touch stock ───────────────────────────

test('transitioning from pending to confirmed does not affect stock', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order = Order::factory()->create(['status' => 'pending']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 2,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'confirmed')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(10);
});

test('transitioning from pending to shipped does not restore stock', function () {
    $product = Product::factory()->create(['stock' => 8]);
    $order = Order::factory()->create(['status' => 'pending']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 2,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'shipped')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(8);
});

// ── Items without product_id skip stock restoration ───────────────────────────

test('cancelling order with custom item (no product_id) does not error', function () {
    $order = Order::factory()->create(['status' => 'confirmed']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => null,
        'product_name' => 'Article sur mesure',
        'price' => 15000,
        'quantity' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.index')
        ->call('updateStatus', $order->id, 'cancelled');

    expect($order->fresh()->status)->toBe('cancelled');
});

test('cancelling order with mixed items (some without product_id) restores correctly', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order = Order::factory()->create(['status' => 'confirmed']);

    // Product item
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    // Custom item
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => null,
        'product_name' => 'Custom Service',
        'price' => 5000,
        'quantity' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(13);
    expect($order->fresh()->status)->toBe('cancelled');
});

// ── Invalid status values are rejected without touching stock ─────────────────

test('invalid status string does not change order or stock', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => 'confirmed']);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.index')
        ->call('updateStatus', $order->id, 'hacked');

    expect($product->fresh()->stock)->toBe(5)
        ->and($order->fresh()->status)->toBe('confirmed');
});

// ── Multiple order cancellations (concurrency simulation) ──────────────────────

test('cancelling multiple orders restores stock correctly for each', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order1 = Order::factory()->create(['status' => 'confirmed']);
    $order2 = Order::factory()->create(['status' => 'confirmed']);

    OrderItem::create([
        'order_id' => $order1->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 2,
    ]);

    OrderItem::create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order1])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order2])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    expect($product->fresh()->stock)->toBe(15); // 10 + 2 + 3
});
