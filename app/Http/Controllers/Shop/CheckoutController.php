<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\NewOrderAdmin;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private function sendOrderEmails(Order $order): void
    {
        if (! config('mail.mailers.smtp.host')) {
            return;
        }

        try {
            if (Setting::get('notify_client_on_order') === '1' && $order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderConfirmed($order));
            }

            if (Setting::get('notify_admin_on_order') === '1') {
                $adminEmail = config('mail.from.address');
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new NewOrderAdmin($order));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order emails for order #'.$order->id.': '.$e->getMessage());
        }
    }

    public function index(): View
    {
        $zones = collect(Setting::shippingZones())
            ->map(fn ($zone, $index) => (object) [
                'id' => $index + 1,
                'name' => $zone['label'] ?? '',
                'delivery_fee' => $zone['price'] ?? 0,
            ]);

        return view('shop.checkout', compact('zones'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer.first_name' => 'required|string',
            'customer.last_name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.email' => 'nullable|email',
            'delivery.zone_id' => 'required|numeric',
            'delivery.address' => 'required|string',
            'delivery.notes' => 'nullable|string',
            'payment.method' => 'required|in:cash,wave,intech',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.quantity_kg' => 'nullable|integer|min:1',
        ]);

        $requestedItems = collect($validated['items']);
        $products = Product::query()
            ->whereIn('id', $requestedItems->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        if ($products->count() !== $requestedItems->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Certains produits de votre panier ne sont plus disponibles.',
            ], 422);
        }

        // Stock availability check
        foreach ($requestedItems as $item) {
            $product = $products->get($item['product_id']);
            $quantity = (int) ($item['quantity'] ?? $item['quantity_kg'] ?? 1);

            if ($product->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuffisant pour \"{$product->name}\". Seulement {$product->stock} disponible(s).",
                ], 422);
            }
        }

        $zones = collect(Setting::shippingZones())->values();
        $selectedZone = $zones->get(((int) $validated['delivery']['zone_id']) - 1);
        $deliveryFee = (int) ($selectedZone['price'] ?? 0);

        $order = Order::create([
            'customer_name' => $validated['customer']['first_name'].' '.$validated['customer']['last_name'],
            'customer_phone' => $validated['customer']['phone'],
            'customer_email' => $validated['customer']['email'] ?? null,
            'customer_address' => $validated['delivery']['address'],
            'city' => $selectedZone['label'] ?? '',
            'delivery_address' => $validated['delivery']['address'],
            'delivery_notes' => $validated['delivery']['notes'] ?? null,
            'delivery_zone' => (string) $validated['delivery']['zone_id'],
            'payment_method' => $validated['payment']['method'],
            'delivery_fee' => $deliveryFee,
            'total' => 0,
            'status' => 'pending',
        ]);

        $subtotal = 0;

        foreach ($requestedItems as $item) {
            $product = $products->get($item['product_id']);
            $quantity = (int) ($item['quantity'] ?? $item['quantity_kg'] ?? 1);
            $lineTotal = $product->price * $quantity;
            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);

            Product::where('id', $product->id)->decrement('stock', $quantity);
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $deliveryFee,
        ]);

        $order->load('items');
        $this->sendOrderEmails($order);

        return response()->json([
            'success' => true,
            'redirect_url' => URL::signedRoute('confirmation', $order),
        ]);
    }
}
