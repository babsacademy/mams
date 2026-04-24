<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $successMessage = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->successMessage = 'Mot de passe mis à jour avec succès.';
    }
}; ?>

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
    @if($successMessage ?? false)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $successMessage }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-10">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.lock-closed class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Sécurité du compte</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Mot de passe actuel</label>
                    <flux:input
                        wire:model="current_password"
                        type="password"
                        variant="filled"
                        placeholder="Entrez votre mot de passe actuel"
                        class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold"
                        required
                    />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nouveau mot de passe</label>
                    <flux:input
                        wire:model="password"
                        type="password"
                        variant="filled"
                        placeholder="Entrez votre nouveau mot de passe"
                        class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold"
                        required
                    />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Confirmer le mot de passe</label>
                    <flux:input
                        wire:model="password_confirmation"
                        type="password"
                        variant="filled"
                        placeholder="Confirmez votre nouveau mot de passe"
                        class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold"
                        required
                    />
                </div>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl">
                <p class="text-[10px] font-black text-blue-900 dark:text-blue-200 uppercase tracking-widest mb-1">Conseil de sécurité</p>
                <p class="text-xs text-blue-800 dark:text-blue-300">Utilisez un mot de passe long et complexe contenant majuscules, minuscules, chiffres et caractères spéciaux.</p>
            </div>
        </div>

        <div class="pt-4">
            <flux:button wire:click="updatePassword" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-primary/20">
                Mettre à jour le mot de passe
            </flux:button>
        </div>
    </div>
</div>
