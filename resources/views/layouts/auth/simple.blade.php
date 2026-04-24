<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-8 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                {{-- Logo & Brand --}}
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-3" wire:navigate>
                    <div class="text-2xl font-bold text-white">{{ config('app.name') }}</div>
                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-[0.3em]">Espace Administration</span>
                </a>

                {{-- Login Card --}}
                <div class="bg-zinc-900/80 backdrop-blur-sm border border-zinc-800 rounded-2xl p-8">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                <p class="text-center text-[10px] text-zinc-600 uppercase tracking-widest">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                </p>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
