<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function index(): View
    {
        return view('shop.tracking');
    }

    public function track(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', strtoupper($orderNumber))->firstOrFail();

        $step = match ($order->status) {
            'pending' => 0,
            'confirmed' => 1,
            'shipped' => 2,
            'delivered' => 3,
            default => 0,
        };

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'step' => $step,
            'placed_at' => $order->created_at?->format('d/m/Y à H:i'),
        ]);
    }
}
