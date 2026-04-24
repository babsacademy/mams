<x-layouts::auth :title="'Connexion'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Connexion" description="Entrez vos identifiants pour accéder au tableau de bord." />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email -->
            <flux:input
                name="email"
                label="Adresse email"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="admin@sacochechic.sn"
            />

            <!-- Mot de passe -->
            <div class="relative">
                <flux:input
                    name="password"
                    label="Mot de passe"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs end-0" :href="route('password.request')" wire:navigate>
                        Mot de passe oublié ?
                    </flux:link>
                @endif
            </div>

            <!-- Se souvenir de moi -->
            <flux:checkbox name="remember" label="Rester connecté" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full !bg-brand-primary hover:!bg-brand-primary/90 border-none font-bold uppercase tracking-widest text-xs !py-3 shadow-lg shadow-brand-primary/20" data-test="login-button">
                    Se connecter
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
