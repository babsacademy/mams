<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Utilisateurs')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $successMessage = '';

    public string $newName = '';
    public string $newEmail = '';
    public string $newPassword = '';
    public bool $newIsAdmin = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createUser(): void
    {
        Gate::authorize('admin-action');

        $this->validate([
            'newName'     => ['required', 'string', 'max:255'],
            'newEmail'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'newPassword' => ['required', Password::min(8)],
        ], [
            'newName.required'     => 'Le nom est obligatoire.',
            'newEmail.required'    => 'L\'email est obligatoire.',
            'newEmail.unique'      => 'Cet email est déjà utilisé.',
            'newPassword.required' => 'Le mot de passe est obligatoire.',
        ]);

        User::create([
            'name'              => $this->newName,
            'email'             => $this->newEmail,
            'password'          => Hash::make($this->newPassword),
            'is_admin'          => $this->newIsAdmin,
            'email_verified_at' => now(),
        ]);

        $this->reset(['newName', 'newEmail', 'newPassword', 'newIsAdmin']);
        $this->successMessage = "Utilisateur créé avec succès.";
        $this->dispatch('close-modal', name: 'create-user');
    }

    public function toggleAdmin(int $userId): void
    {
        Gate::authorize('admin-action');

        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_admin' => ! $user->is_admin]);
        $this->successMessage = $user->is_admin
            ? "{$user->name} est maintenant administrateur."
            : "{$user->name} n'est plus administrateur.";
    }

    public function deleteUser(int $userId): void
    {
        Gate::authorize('admin-action');

        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return;
        }

        $user->delete();
        $this->successMessage = "Utilisateur supprimé.";
    }

    #[Computed]
    public function users(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', "%{$this->search}%")
                   ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);
    }
}; ?>

<div class="space-y-10" x-data>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Utilisateurs</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Gérez les accès et les permissions des membres du staff et des clients.</p>
        </div>
        <flux:modal.trigger name="create-user">
            <flux:button variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-primary/20">
                <flux:icon.plus class="size-4 mr-2" />
                Ajouter un membre
            </flux:button>
        </flux:modal.trigger>
    </div>

    @if($successMessage)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $successMessage }}</p>
        </div>
    @endif

    {{-- Filtres & Recherche --}}
    <div class="flex items-center gap-4">
        <div class="flex-1 max-w-md">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                variant="filled" 
                placeholder="Rechercher par nom ou email…" 
                icon="magnifying-glass" 
                class="!bg-white dark:!bg-zinc-900 !h-12 font-bold !rounded-2xl border-zinc-200 dark:border-zinc-800" 
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-500">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                        <th class="text-left pl-10 pr-6 py-6 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Profil</th>
                        <th class="text-left px-6 py-6 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Coordonnées</th>
                        <th class="text-left px-6 py-6 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap text-center">Rôle</th>
                        <th class="text-left px-6 py-6 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Inscription</th>
                        <th class="text-right pl-6 pr-10 py-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                    @forelse($this->users as $user)
                        <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors duration-200">
                            <td class="pl-10 pr-6 py-6 whitespace-nowrap">
                                <div class="flex items-center gap-5">
                                    <div class="size-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-black text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden group-hover:scale-110 transition-transform duration-500">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $user->name }}</p>
                                            @if($user->id === auth()->id())
                                                <span class="px-2 py-0.5 bg-brand-primary/10 text-brand-primary text-[8px] font-black uppercase tracking-widest rounded-md">Moi</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-0.5">ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="text-zinc-600 dark:text-zinc-300 font-bold">{{ $user->email }}</span>
                                    <span class="text-[9px] text-zinc-400 font-black uppercase tracking-widest">Compte Vérifié</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center whitespace-nowrap">
                                @if($user->is_admin)
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-500 text-[9px] font-black uppercase tracking-widest rounded-full">
                                        <div class="size-1 bg-amber-500 rounded-full animate-pulse"></div>
                                        Administrateur
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 text-[9px] font-black uppercase tracking-widest rounded-full">
                                        Client Privilège
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                                {{ $user->created_at->translatedFormat('d F Y') }}
                            </td>
                            <td class="pl-6 pr-10 py-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300">
                                    @if($user->id !== auth()->id())
                                        <button
                                            wire:click="toggleAdmin({{ $user->id }})"
                                            wire:confirm="{{ $user->is_admin ? 'Retirer les droits admin à ' . $user->name . ' ?' : 'Donner les droits admin à ' . $user->name . ' ?' }}"
                                            class="size-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors"
                                            title="Modifier les droits"
                                        >
                                            <flux:icon.shield-check class="size-5" />
                                        </button>

                                        <button
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Supprimer définitivement {{ $user->name }} ? Cette action est irréversible."
                                            class="size-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                                            title="Supprimer le compte"
                                        >
                                            <flux:icon.trash class="size-5" />
                                        </button>
                                    @else
                                        <span class="text-[9px] text-zinc-400 font-black uppercase tracking-widest italic pr-4">Action non autorisée</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-24 text-center">
                                <div class="size-16 bg-zinc-50 dark:bg-zinc-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                    <flux:icon.users class="size-8 text-zinc-200 dark:text-zinc-700" />
                                </div>
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucun membre trouvé</p>
                                <p class="text-xs text-zinc-400 mt-2">Essayez d'ajuster votre recherche.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->users->hasPages())
            <div class="px-10 py-6 border-t border-zinc-50 dark:border-zinc-800 bg-zinc-50/10 dark:bg-zinc-900/10">
                {{ $this->users->links() }}
            </div>
        @endif
    </div>

    <flux:modal name="create-user" class="w-full max-w-lg">
    <div class="p-8 space-y-6">
        <div>
            <h2 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un membre</h2>
            <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-1">Créer un nouveau compte utilisateur.</p>
        </div>

        <form wire:submit="createUser" class="space-y-5">
            <flux:field>
                <flux:label class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Nom complet</flux:label>
                <flux:input wire:model="newName" placeholder="Ex: Awa Ndiaye" />
                <flux:error name="newName" />
            </flux:field>

            <flux:field>
                <flux:label class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Adresse email</flux:label>
                <flux:input wire:model="newEmail" type="email" placeholder="exemple@email.com" />
                <flux:error name="newEmail" />
            </flux:field>

            <flux:field>
                <flux:label class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Mot de passe</flux:label>
                <flux:input wire:model="newPassword" type="password" placeholder="Minimum 8 caractères" />
                <flux:error name="newPassword" />
            </flux:field>

            <flux:field variant="inline">
                <flux:checkbox wire:model="newIsAdmin" />
                <flux:label class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Accès administrateur</flux:label>
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost" class="font-black uppercase tracking-widest text-[10px]">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px]">
                    Créer le compte
                </flux:button>
            </div>
        </form>
    </div>
    </flux:modal>
</div>

