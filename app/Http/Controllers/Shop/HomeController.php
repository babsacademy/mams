<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::query()
                ->where('is_active', true)
                ->latest()
                ->take(8)
                ->get();
        }

        $newArrivals = Product::query()
            ->where('is_active', true)
            ->where('is_new', true)
            ->latest()
            ->take(6)
            ->get();

        $productsWithVideos = Product::query()
            ->where('is_active', true)
            ->whereHas('media', fn ($query) => $query->where('type', 'video'))
            ->with('media')
            ->latest()
            ->take(6)
            ->get();

        $heroImagePath = Setting::get('hero_image_url', null);
        $heroImageUrl = Setting::resolveMediaUrl($heroImagePath) ?? asset('mams-template/assets/images/hero.png');
        $heroImagePositionX = max(0, min(100, (int) Setting::get('hero_image_position_x', 50)));
        $heroImagePositionY = max(0, min(100, (int) Setting::get('hero_image_position_y', 20)));
        $heroBadge = Setting::get('hero_badge', 'Mams Store World');
        $heroTitleLine1 = Setting::get('hero_title_line1', 'Revele ta');
        $heroTitleLine2 = Setting::get('hero_title_line2', 'beaute');
        $heroDescription = Setting::get('hero_description', "Une vitrine premium pour la beaute, les cheveux et le style, pensee pour une clientele qui aime l'allure editoriale.");
        $heroCta1Text = Setting::get('hero_cta1_text', 'Decouvrir la collection');
        $heroCta2Text = Setting::get('hero_cta2_text', 'Pourquoi nous');
        $instagramUrl = Setting::get('instagram_url', '#');

        $editorialImageLeft = Setting::resolveMediaUrl(Setting::get('editorial_image_left', '')) ?? asset('mams-template/assets/images/prod.png');
        $editorialImageRight = Setting::resolveMediaUrl(Setting::get('editorial_image_right', '')) ?? asset('mams-template/assets/images/pr.png');
        $editorialBadge = Setting::get('editorial_badge', 'Collections');
        $editorialTitle = Setting::get('editorial_title', 'Nos produits, votre beauté');
        $editorialText = Setting::get('editorial_text', 'Découvrez notre sélection premium de cheveux, perruques et accessoires beauté pensée pour sublimer chaque style.');
        $editorialLinkText = Setting::get('editorial_link_text', 'Explorer la boutique');

        return view('shop.home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'productsWithVideos' => $productsWithVideos,
            'heroImageUrl' => $heroImageUrl,
            'heroImagePositionX' => $heroImagePositionX,
            'heroImagePositionY' => $heroImagePositionY,
            'heroBadge' => $heroBadge,
            'heroTitleLine1' => $heroTitleLine1,
            'heroTitleLine2' => $heroTitleLine2,
            'heroDescription' => $heroDescription,
            'heroCta1Text' => $heroCta1Text,
            'heroCta2Text' => $heroCta2Text,
            'instagramUrl' => $instagramUrl,
            'editorialImageLeft' => $editorialImageLeft,
            'editorialImageRight' => $editorialImageRight,
            'editorialBadge' => $editorialBadge,
            'editorialTitle' => $editorialTitle,
            'editorialText' => $editorialText,
            'editorialLinkText' => $editorialLinkText,
        ]);
    }
}
