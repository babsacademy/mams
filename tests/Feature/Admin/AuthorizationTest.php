<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

// ── Orders Index ──────────────────────────────────────────────────────────────

test('non-admin cannot call updateStatus on orders index', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($this->user)
        ->test('admin.orders.index')
        ->call('updateStatus', $order->id, 'confirmed')
        ->assertForbidden();
});

test('admin can call updateStatus on orders index', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.index')
        ->call('updateStatus', $order->id, 'confirmed');

    expect($order->fresh()->status)->toBe('confirmed');
});

// ── Orders Show ───────────────────────────────────────────────────────────────

test('non-admin cannot call updateStatus on orders show', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($this->user)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'confirmed')
        ->call('updateStatus')
        ->assertForbidden();
});

test('admin can call updateStatus on orders show', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($this->admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'confirmed')
        ->call('updateStatus');

    expect($order->fresh()->status)->toBe('confirmed');
});

// ── Users Index ───────────────────────────────────────────────────────────────

test('non-admin cannot toggleAdmin', function () {
    $target = User::factory()->create(['is_admin' => false]);

    Livewire::actingAs($this->user)
        ->test('admin.users.index')
        ->call('toggleAdmin', $target->id)
        ->assertForbidden();

    expect($target->fresh()->is_admin)->toBeFalse();
});

test('admin can toggleAdmin', function () {
    $target = User::factory()->create(['is_admin' => false]);

    Livewire::actingAs($this->admin)
        ->test('admin.users.index')
        ->call('toggleAdmin', $target->id);

    expect($target->fresh()->is_admin)->toBeTrue();
});

test('non-admin cannot deleteUser', function () {
    $target = User::factory()->create();

    Livewire::actingAs($this->user)
        ->test('admin.users.index')
        ->call('deleteUser', $target->id)
        ->assertForbidden();

    expect(User::find($target->id))->not->toBeNull();
});

test('admin can deleteUser', function () {
    $target = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('admin.users.index')
        ->call('deleteUser', $target->id);

    expect(User::find($target->id))->toBeNull();
});

// ── Products Index ────────────────────────────────────────────────────────────

test('non-admin cannot toggleActive on products', function () {
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->user)
        ->test('admin.products.index')
        ->call('toggleActive', $product->id)
        ->assertForbidden();

    expect($product->fresh()->is_active)->toBeTrue();
});

test('admin can toggleActive on products', function () {
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test('admin.products.index')
        ->call('toggleActive', $product->id);

    expect($product->fresh()->is_active)->toBeFalse();
});

test('non-admin cannot deleteProduct', function () {
    $product = Product::factory()->create();

    Livewire::actingAs($this->user)
        ->test('admin.products.index')
        ->call('confirmDelete', $product->id)
        ->call('deleteProduct')
        ->assertForbidden();

    expect(Product::find($product->id))->not->toBeNull();
});

test('admin can deleteProduct', function () {
    $product = Product::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('admin.products.index')
        ->call('confirmDelete', $product->id)
        ->call('deleteProduct');

    expect(Product::find($product->id))->toBeNull();
});

// ── Categories ────────────────────────────────────────────────────────────────

test('non-admin cannot save category', function () {
    Livewire::actingAs($this->user)
        ->test('admin.categories.index')
        ->set('name', 'New Category')
        ->set('isActive', true)
        ->call('save')
        ->assertForbidden();

    expect(Category::count())->toBe(0);
});

test('admin can save category', function () {
    Livewire::actingAs($this->admin)
        ->test('admin.categories.index')
        ->set('name', 'New Category')
        ->set('isActive', true)
        ->call('save');

    expect(Category::count())->toBe(1);
    expect(Category::first()->name)->toBe('New Category');
});

test('non-admin cannot toggleActive on categories', function () {
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->user)
        ->test('admin.categories.index')
        ->call('toggleActive', $category->id)
        ->assertForbidden();

    expect($category->fresh()->is_active)->toBeTrue();
});

test('non-admin cannot deleteCategory', function () {
    $category = Category::factory()->create();

    Livewire::actingAs($this->user)
        ->test('admin.categories.index')
        ->call('confirmDelete', $category->id)
        ->call('deleteCategory')
        ->assertForbidden();

    expect(Category::find($category->id))->not->toBeNull();
});

// ── Promotions/Coupons ────────────────────────────────────────────────────────

test('admin can deleteCategory', function () {
    $category = Category::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('admin.categories.index')
        ->call('confirmDelete', $category->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteCategory')
        ->assertSet('showDeleteModal', false);

    expect(Category::find($category->id))->toBeNull();
});

test('non-admin cannot save coupon', function () {
    Livewire::actingAs($this->user)
        ->test('admin.promotions.index')
        ->set('code', 'TEST10')
        ->set('type', 'percent')
        ->set('value', 10)
        ->set('isActive', true)
        ->call('save')
        ->assertForbidden();

    expect(Coupon::count())->toBe(0);
});

test('admin can save coupon', function () {
    Livewire::actingAs($this->admin)
        ->test('admin.promotions.index')
        ->set('code', 'TEST10')
        ->set('type', 'percent')
        ->set('value', 10)
        ->set('isActive', true)
        ->call('save');

    expect(Coupon::count())->toBe(1);
});

test('non-admin cannot toggleActive on coupons', function () {
    $coupon = Coupon::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->user)
        ->test('admin.promotions.index')
        ->call('toggleActive', $coupon->id)
        ->assertForbidden();

    expect($coupon->fresh()->is_active)->toBeTrue();
});
