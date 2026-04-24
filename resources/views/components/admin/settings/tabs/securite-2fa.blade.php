<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-10">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.shield-check class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Authentification à deux facteurs</h3>
            </div>

            <div class="py-8 text-center">
                <div class="size-16 bg-zinc-50 dark:bg-zinc-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <flux:icon.lock-closed class="size-8 text-zinc-300 dark:text-zinc-600" />
                </div>
                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Fonctionnalité en développement</p>
                <p class="text-xs text-zinc-400 mt-2 max-w-sm mx-auto">L'authentification à deux facteurs sera disponible très bientôt pour sécuriser davantage votre compte.</p>
            </div>

            <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl">
                <p class="text-[10px] font-black text-blue-900 dark:text-blue-200 uppercase tracking-widest mb-1">À venir</p>
                <p class="text-xs text-blue-800 dark:text-blue-300">La 2FA vous permettra de protéger votre compte avec une couche de sécurité supplémentaire via une application d'authentification ou SMS.</p>
            </div>
        </div>
    </div>
</div>
