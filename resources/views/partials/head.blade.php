<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
    // Force dark mode for admin dashboard
    if (document.documentElement.classList.contains('dark') === false) {
        document.documentElement.classList.add('dark');
    }
</script>

@php
    $colorPrimary = \App\Models\Setting::get('color_primary', '#6366f1');
    $colorHover = \App\Models\Setting::get('color_primary_hover', '#4f46e5');
@endphp
<style>
    :root {
        --color-brand-primary: {{ $colorPrimary }};
        --color-brand-primary-hover: {{ $colorHover }};
    }
</style>

@fluxAppearance
