<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;

class ConfirmationController extends Controller
{
    public function show(Order $order): \Illuminate\View\View
    {
        // Vérifier que la commande existe
        if (!$order || !$order->exists) {
            return redirect()->route('home')->with('error', 'Commande introuvable.');
        }

        $order->load('items.product');

        return view('shop.confirmation', compact('order'));
    }
}
