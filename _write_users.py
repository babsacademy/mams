#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Write the improved users index blade file."""

import os

content = '''<?php

use App\\Models\\User;
use Livewire\\Attributes\\Computed;
use Livewire\\Attributes\\Layout;
use Livewire\\Attributes\\Title;
use Livewire\\Attributes\\Url;
use Livewire\\Component;
use Livewire\\WithPagination;

new #[Title('Utilisateurs')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $successMessage = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleAdmin(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return; // Cannot demote yourself
        }

        $user->update(['is_admin' => ! $user->is_admin]);
        $this->successMessage = $user->is_admin
            ? "{$user->name} est maintenant administrateur."
            : "{$user->name} n\\'est plus administrateur.";
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return; // Cannot delete yourself
        }

        $user->delete();
        $this->successMessage = "Utilisateur supprim\\u00e9.";
    }

    #[Computed]
    public function users(): \\Illuminate\\Pagination\\LengthAwarePaginator
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

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Utilisateurs</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">G\\u00e9rez les acc\\u00e8s et les permissions des membres du staff et des clients.</p>
        </div>
        <flux:button href="#" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-pink/20">
            <flux:icon.plus class="size-4 mr-2" />
            Ajouter un membre
        </flux:button>
    </div>

    @if($successMessage)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
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
                placeholder="Rechercher par nom ou email\\u2026"
                icon="magnifying-glass"
                class="!bg-white dark:!bg-zinc-900 !h-11 font-bold !rounded-2xl border-zinc-200 dark:border-zinc-800"
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <th class="text-left pl-8 pr-6 py-5 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Profil</th>
                        <th class="text-left px-6 py-5 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Coordonn\\u00e9es</th>
                        <th class="text-center px-6 py-5 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">R\\u00f4le</th>
                        <th class="text-left px-6 py-5 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] whitespace-nowrap">Inscription</th>
                        <th class="text-right pl-6 pr-8 py-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($this->users as $user)
                        <tr class="group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors duration-200">
                            <td class="pl-8 pr-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-black text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden group-hover:scale-110 transition-transform duration-500">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $user->name }}</p>
                                            @if($user->id === auth()->id())
                                                <span class="px-2 py-0.5 bg-brand-pink/10 text-brand-pink text-[8px] font-black uppercase tracking-widest rounded-md">Moi</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-0.5">ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="text-zinc-600 dark:text-zinc-300 font-bold">{{ $user->email }}</span>
                                    <span class="text-[9px] text-zinc-400 font-black uppercase tracking-widest">Compte V\\u00e9rifi\\u00e9</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                @if($user->is_admin)
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-brand-pink/10 border border-brand-pink/20 text-brand-pink text-[9px] font-black uppercase tracking-widest rounded-full">
                                        <div class="size-1 bg-brand-pink rounded-full animate-pulse"></div>
                                        Administrateur
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 text-[9px] font-black uppercase tracking-widest rounded-full">
                                        Client Privil\\u00e8ge
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                                {{ $user->created_at->translatedFormat('d F Y') }}
                            </td>
                            <td class="pl-6 pr-8 py-5 text-right whitespace-nowrap">
                                @if($user->id !== auth()->id())
                                    <div class="flex items-center justify-end gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                        <button
                                            wire:click="toggleAdmin({{ $user->id }})"
                                            wire:confirm="{{ $user->is_admin ? 'Retirer les droits admin \\u00e0 ' . $user->name . ' ?' : 'Donner les droits admin \\u00e0 ' . $user->name . ' ?' }}"
                                            class="size-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 hover:text-brand-pink hover:bg-brand-pink/10 transition-colors"
                                            title="Modifier les droits"
                                        >
                                            <flux:icon.shield-check class="size-5" />
                                        </button>

                                        <button
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Supprimer d\\u00e9finitivement {{ $user->name }} ? Cette action est irr\\u00e9versible."
                                            class="size-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                                            title="Supprimer le compte"
                                        >
                                            <flux:icon.trash class="size-5" />
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <flux:icon.users class="size-10 text-zinc-200 dark:text-zinc-800 mx-auto mb-4" />
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucun membre trouv\\u00e9</p>
                                <p class="text-xs text-zinc-400 mt-1">Essayez d'ajuster votre recherche.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->users->hasPages())
            <div class="px-8 py-5 border-t border-zinc-100 dark:border-zinc-800">
                {{ $this->users->links() }}
            </div>
        @endif
    </div>
</div>
'''

# The file path with the lightning emoji
dirpath = r'c:\dev\schic\resources\views\components\admin\users'
filename = '\u26a1index.blade.php'
filepath = os.path.join(dirpath, filename)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f'Written to: {filepath}')
print(f'Size: {os.path.getsize(filepath)} bytes')
