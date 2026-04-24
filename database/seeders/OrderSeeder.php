<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $sampleOrders = [
            [
                'customer_name' => 'Awa Ndiaye',
                'customer_phone' => '771112233',
                'customer_email' => 'awa.ndiaye@example.com',
                'city' => 'Dakar',
                'delivery_address' => 'Point E, Dakar',
                'delivery_notes' => 'Livraison en fin de journee',
                'payment_method' => 'wave',
                'status' => 'confirmed',
                'items' => [
                    ['slug' => 'lace-frontal-body-wave-24', 'quantity' => 1],
                ],
            ],
            [
                'customer_name' => 'Fatou Diop',
                'customer_phone' => '776667788',
                'customer_email' => 'fatou.diop@example.com',
                'city' => 'Dakar',
                'delivery_address' => 'Mermoz, Dakar',
                'delivery_notes' => 'Appeler avant de livrer',
                'payment_method' => 'cash',
                'status' => 'pending',
                'items' => [
                    ['slug' => 'hd-lace-straight-bob-12', 'quantity' => 1],
                    ['slug' => 'elastic-band-wig-cap-duo', 'quantity' => 2],
                ],
            ],
            [
                'customer_name' => 'Coumba Fall',
                'customer_phone' => '782223344',
                'customer_email' => 'coumba.fall@example.com',
                'city' => 'Dakar',
                'delivery_address' => 'Parcelles Assainies, Dakar',
                'delivery_notes' => 'Livraison rapide svp',
                'payment_method' => 'wave',
                'status' => 'delivered',
                'items' => [
                    ['slug' => '3-bundles-deep-wave-18-20-22', 'quantity' => 1],
                    ['slug' => 'hd-closure-5x5-natural-curly', 'quantity' => 1],
                ],
            ],
            [
                'customer_name' => 'Marieme Sy',
                'customer_phone' => '704445566',
                'customer_email' => 'marieme.sy@example.com',
                'city' => 'Saint-Louis',
                'delivery_address' => 'Saint-Louis, Senegal',
                'delivery_notes' => 'Expedier en region',
                'payment_method' => 'intech',
                'status' => 'shipped',
                'items' => [
                    ['slug' => 'water-wave-26-signature', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($sampleOrders as $sampleOrder) {
            $existingOrder = Order::query()
                ->where('customer_name', $sampleOrder['customer_name'])
                ->where('delivery_address', $sampleOrder['delivery_address'])
                ->first();

            if ($existingOrder) {
                continue;
            }

            $subtotal = 0;
            $resolvedItems = [];

            foreach ($sampleOrder['items'] as $item) {
                $product = Product::query()->where('slug', $item['slug'])->first();

                if (! $product) {
                    continue;
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;
                $resolvedItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
            }

            if ($resolvedItems === []) {
                continue;
            }

            $deliveryFee = str_contains(strtolower($sampleOrder['delivery_address']), 'dakar') ? 2000 : 3500;

            $order = Order::create([
                'customer_name' => $sampleOrder['customer_name'],
                'customer_phone' => $sampleOrder['customer_phone'],
                'customer_email' => $sampleOrder['customer_email'],
                'customer_address' => $sampleOrder['delivery_address'],
                'city' => $sampleOrder['city'],
                'notes' => $sampleOrder['delivery_notes'],
                'delivery_zone' => str_contains(strtolower($sampleOrder['delivery_address']), 'dakar') ? 'dakar' : 'regions',
                'delivery_address' => $sampleOrder['delivery_address'],
                'delivery_city' => $sampleOrder['city'],
                'delivery_notes' => $sampleOrder['delivery_notes'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'status' => $sampleOrder['status'],
                'payment_method' => $sampleOrder['payment_method'],
                'placed_at' => now()->subDays(fake()->numberBetween(1, 20)),
            ]);

            foreach ($resolvedItems as $resolvedItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $resolvedItem['product']->id,
                    'product_name' => $resolvedItem['product']->name,
                    'price' => $resolvedItem['product']->price,
                    'quantity' => $resolvedItem['quantity'],
                ]);
            }
        }
    }
}
