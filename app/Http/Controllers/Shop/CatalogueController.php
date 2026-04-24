<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class CatalogueController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->get();

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->with('category');

        if (request('category')) {
            $category = Category::where('slug', request('category'))->first();
            if ($category) {
                $productsQuery->where('category_id', $category->id);
            }
        }

        $products = $productsQuery->paginate(12);

        return view('shop.catalogue', compact('products', 'categories'));
    }
}
