<flux:dropdown position="bottom" align="start">
    <button type="button" class="w-full flex items-center gap-3 p-2 rounded-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all group">
        <flux:avatar
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            class="!size-9 shadow-sm"
        />
        <div class="flex-1 text-left">
            <p class="text-[10px] font-black text-zinc-900 dark:text-white uppercase tracking-tight truncate">{{ auth()->user()->name }}</p>
            <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-widest truncate">{{ auth()->user()->email }}</p>
        </div>
        <flux:icon.chevron-up-down class="size-4 text-zinc-400 group-hover:text-zinc-600 transition-colors" />
    </button>

    <flux:menu class="!rounded-3xl !p-2 !shadow-2xl border-zinc-200 dark:border-zinc-800">
        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate class="!rounded-2xl font-black uppercase text-[10px] tracking-widest py-3">
            {{ __('Settings') }}
        </flux:menu.item>

        <flux:menu.separator class="!my-2" />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer !rounded-2xl font-black uppercase text-[10px] tracking-widest py-3 !text-rose-500 hover:!bg-rose-50 dark:hover:!bg-rose-500/10 transition-colors"
                data-test="logout-button"
            >
                {{ __('Log out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
