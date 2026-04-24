@php
use App\Models\Setting;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Accueil</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 to-slate-800">
        <div class="text-center text-white px-4">
            @if(Setting::get('hero_badge'))
                <span class="inline-block px-3 py-1 mb-4 text-sm font-semibold bg-blue-500/20 text-blue-300 rounded-full">
                    {{ Setting::get('hero_badge') }}
                </span>
            @endif

            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                {{ Setting::get('hero_title_line1', 'Le cuir') }}<br>
                {{ Setting::get('hero_title_line2', 'réinventé.') }}
            </h1>

            @if(Setting::get('hero_description'))
                <p class="text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
                    {{ Setting::get('hero_description') }}
                </p>
            @endif
        </div>
    </section>
</body>
</html>
