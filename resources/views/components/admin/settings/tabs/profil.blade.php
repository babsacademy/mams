<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->successMessage = 'Profil mis à jour avec succès.';
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
        $this->successMessage = 'Lien de vérification envoyé.';
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
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
                    <flux:icon.user class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Informations personnelles</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nom complet</label>
                    <flux:input wire:model="name" variant="filled" placeholder="Votre nom" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" required />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Adresse email</label>
                    <flux:input wire:model="email" type="email" variant="filled" placeholder="Votre email" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" required />
                </div>

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-xl">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200 mb-2">Votre adresse email n'est pas vérifiée.</p>
                        <flux:button wire:click="resendVerificationNotification" variant="subtle" class="!text-amber-600 dark:!text-amber-400 !text-sm">
                            Renvoyer le lien de vérification
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-4">
            <flux:button wire:click="updateProfileInformation" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-primary/20">
                Enregistrer les modifications
            </flux:button>
        </div>
    </div>
</div>
