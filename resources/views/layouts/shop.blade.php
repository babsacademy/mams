<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    @php
        $brandName = $siteInfo['shop_name'] ?? 'Mams Store World';
        $defaultOgImage = $siteInfo['logo_url'] ?? asset('mams-template/assets/images/hero.png');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $brandName . ' | Hair, beauty, style')</title>
    <meta name="description" content="@yield('description', $brandName . ' propose une experience premium pour la beaute, les cheveux et le style.')">
    <meta name="keywords" content="@yield('keywords', 'beauty, hair, wigs, premium, dakar, boutique')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="author" content="{{ $brandName }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $brandName . ' | Hair, beauty, style')">
    <meta property="og:description" content="@yield('og_description', $brandName . ' propose une experience premium pour la beaute, les cheveux et le style.')">
    <meta property="og:image" content="@yield('og_image', $defaultOgImage)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $brandName)">
    <meta name="twitter:description" content="@yield('twitter_description', $brandName . ' propose une experience premium pour la beaute, les cheveux et le style.')">
    <meta name="twitter:image" content="@yield('twitter_image', $defaultOgImage)">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    {{-- Hybrid favicon: ICO pour les vieux navigateurs, SVG/WebP pour les modernes --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    @if(!empty($siteInfo['favicon_url']))
        @php $favType = str_ends_with($siteInfo['favicon_url'], '.svg') ? 'image/svg+xml' : 'image/png'; @endphp
        <link rel="icon" type="{{ $favType }}" href="{{ $siteInfo['favicon_url'] }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ $siteInfo['logo_url'] ?? asset('favicon.svg') }}">
    @endif
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mams: {
                            black: '#0d0d0d',
                            gold: '#c9a96e',
                            sand: '#f5f0e8',
                            slate: '#171717',
                            card: '#111111',
                        },
                    },
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        display: ['"Playfair Display"', 'serif'],
                    },
                    boxShadow: {
                        gold: '0 18px 40px rgba(201, 169, 110, 0.16)',
                    },
                    backgroundImage: {
                        'mams-hero': 'radial-gradient(circle at 10% 20%, #2b2b2b 0%, #111 42%, #0b0b0b 100%)',
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="{{ asset('mams-template/assets/css/custom.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @stack('head')
</head>
@php
    $floatingWhatsApp = ltrim(preg_replace('/[^\d]/', '', $siteInfo['whatsapp_number'] ?? '221771831987'), '+');
    $floatingWhatsAppLink = 'https://wa.me/' . $floatingWhatsApp;
@endphp
<body class="bg-[#0d0d0d] font-sans text-white antialiased" x-data="{ mobileMenuOpen: false }">
    @include('shop.partials.header')

    <main>@yield('content')</main>

    @include('shop.partials.footer')

    <div
        x-data
        x-show="$store.toast.visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="toast"
        x-cloak
    >
        <span x-text="$store.toast.productName ? $store.toast.productName + ' ajoute au panier' : $store.toast.message"></span>
    </div>

    <a
        href="{{ $floatingWhatsAppLink }}"
        target="_blank"
        rel="noopener noreferrer"
        class="whatsapp-float"
        aria-label="Contacter sur WhatsApp"
    >
        <svg class="h-6 w-6 text-black" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <script>
        window.STOREFRONT_BRAND = @json($brandName);
        window.STOREFRONT_WHATSAPP = @json($floatingWhatsApp);
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    @stack('scripts')
</body>
</html>
