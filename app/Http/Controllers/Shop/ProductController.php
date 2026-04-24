<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product): \Illuminate\View\View
    {
        $product->load(['category', 'media']);

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->limit(4)
            ->get();

        return view('shop.produit', compact('product', 'relatedProducts'));
    }
}
