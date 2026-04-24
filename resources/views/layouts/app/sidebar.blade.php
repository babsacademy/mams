<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 font-sans antialiased text-zinc-900 dark:text-zinc-100 transition-colors duration-500 flex flex-col lg:flex-row">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-xl overflow-y-auto h-screen z-20 shrink-0">
            <flux:sidebar.header class="py-6 px-2">
                <x-app-logo :sidebar="true" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="px-3 space-y-0.5">
                @if(auth()->user()->isAdmin())
                    {{-- TABLEAU DE BORD --}}
                    <flux:sidebar.group heading="TABLEAU DE BORD" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-1.5 mt-2 px-3">
                        <flux:sidebar.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Vue d'ensemble
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    {{-- CATALOGUE --}}
                    <flux:sidebar.group heading="CATALOGUE" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-1.5 mt-4 px-3">
                        <flux:sidebar.item icon="shopping-bag" :href="route('admin.products.index')" :current="request()->routeIs('admin.products.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Produits
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tag" :href="route('admin.categories.index')" :current="request()->routeIs('admin.categories.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Catégories
                        </flux:sidebar.item>
                        @if(config('features.media'))
                        <flux:sidebar.item icon="photo" :href="route('admin.media.library')" :current="request()->routeIs('admin.media.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Médiathèque
                        </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    {{-- VENTES --}}
                    <flux:sidebar.group heading="VENTES" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-1.5 mt-4 px-3">
                        <flux:sidebar.item icon="inbox" :href="route('admin.orders.index')" :current="request()->routeIs('admin.orders.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Commandes
                        </flux:sidebar.item>
                        @if(config('features.analytics'))
                        <flux:sidebar.item icon="chart-bar" :href="route('admin.reports.index')" :current="request()->routeIs('admin.reports.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Rapports
                        </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    {{-- CONTENU --}}
                    @if(config('features.storefront') || config('features.promotions'))
                    <flux:sidebar.group heading="CONTENU" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-1.5 mt-4 px-3">
                        @if(config('features.storefront'))
                        <flux:sidebar.item icon="paint-brush" :href="route('admin.storefront.index')" :current="request()->routeIs('admin.storefront.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Vitrine
                        </flux:sidebar.item>
                        @endif
                        @if(config('features.promotions'))
                        <flux:sidebar.item icon="ticket" :href="route('admin.promotions.index')" :current="request()->routeIs('admin.promotions.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Promotions
                        </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                    @endif

                    {{-- GESTION --}}
                    <flux:sidebar.group heading="GESTION" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-1.5 mt-4 px-3">
                        @if(config('features.users'))
                        <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Utilisateurs
                        </flux:sidebar.item>
                        @endif
                        @if(config('features.notifications'))
                        <flux:sidebar.item icon="bell" :href="route('admin.notifications.index')" :current="request()->routeIs('admin.notifications.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Notifications
                        </flux:sidebar.item>
                        @endif
                        <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.index')" :current="request()->routeIs('admin.settings.*')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                            Configuration
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                @if(! auth()->user()->isAdmin())
                <flux:sidebar.group heading="PLATEFORME" class="grid !text-[9px] !font-black !tracking-[0.2em] !text-zinc-400 dark:!text-zinc-600 mb-4 mt-2 px-3">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="!rounded-xl !py-2.5 !px-4 hover:!bg-brand-primary/10 hover:!text-brand-primary transition-all font-bold text-[11px] tracking-wide">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />


            <x-desktop-user-menu class="hidden lg:block border-t border-zinc-100 dark:border-zinc-800 p-4" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
            <flux:header class="lg:hidden z-10 sticky top-0 border-b border-zinc-200 dark:border-zinc-800 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl px-4 py-3 shrink-0">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" variant="ghost" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="!size-10"
                />

                <flux:menu class="!rounded-2xl !p-2 !shadow-2xl border-zinc-200 dark:border-zinc-800">
                    <flux:menu.radio.group>
                        <div class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    class="!size-10"
                                />

                                <div class="grid flex-1 leading-tight">
                                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate">{{ auth()->user()->name }}</h4>
                                    <p class="text-[10px] text-zinc-500 font-medium truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="!my-1" />

                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate class="!rounded-xl font-bold text-[11px] tracking-wide py-2.5">
                        {{ __('Settings') }}
                    </flux:menu.item>

                    <flux:menu.separator class="!my-1" />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer !rounded-xl font-bold text-[11px] tracking-wide py-2.5 !text-rose-500 hover:!bg-rose-50 dark:hover:!bg-rose-500/10 transition-colors"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            </flux:header>

            <flux:main container class="animate-in fade-in duration-300 p-5 lg:p-8 mb-16 flex-1">
                {{ $slot }}
            </flux:main>
        </div>

        @fluxScripts
    </body>
</html>
