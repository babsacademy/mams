<?php

use App\Actions\UpdateOrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Commandes')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $orderId, string $status): void
    {
        Gate::authorize('admin-action');

        $order = Order::findOrFail($orderId);
        (new UpdateOrderStatus)->execute($order, $status);
        $this->dispatch('notify', message: 'Statut mis à jour.');
    }

    #[Computed]
    public function orders(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Order::with('items')
            ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('order_number', 'like', "%{$this->search}%")
                ->orWhere('customer_phone', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);
    }
}; ?>

<div>
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Commandes</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Gérez vos commandes et leurs statuts en temps réel.</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1 relative group">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher par nom, téléphone ou n° de commande…" icon="magnifying-glass" variant="filled" class="!bg-white dark:!bg-zinc-900 border-zinc-200 dark:border-zinc-800 !h-11 font-bold" />
        </div>
        <flux:select wire:model.live="filterStatus" class="sm:w-64 !h-11">
            <flux:select.option value="">Tous les statuts</flux:select.option>
            @foreach(App\Models\Order::STATUSES as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800/50">
                        <th class="text-left pl-10 pr-6 py-6 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Commande</th>
                        <th class="text-left px-6 py-6 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Client</th>
                        <th class="text-left px-6 py-6 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] hidden md:table-cell whitespace-nowrap">Ville</th>
                        <th class="text-right px-6 py-6 w-32 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Total</th>
                        <th class="text-center px-6 py-6 w-48 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Statut</th>
                        <th class="text-right pl-6 pr-10 py-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                    @forelse($this->orders as $order)
                        @php
                            $statusConfig = [
                                'pending'   => ['color' => 'orange', 'label' => 'En attente'],
                                'confirmed' => ['color' => 'blue',   'label' => 'Confirmée'],
                                'shipped'   => ['color' => 'purple', 'label' => 'Expédiée'],
                                'delivered' => ['color' => 'green',  'label' => 'Livrée'],
                                'cancelled' => ['color' => 'red',    'label' => 'Annulée'],
                            ];
                            $current = $statusConfig[$order->status] ?? ['color' => 'zinc', 'label' => $order->status];
                            $colorMap = [
                                'orange' => 'text-orange-600 bg-orange-50 dark:bg-orange-500/10 border-orange-200/50 dark:border-orange-500/20',
                                'blue'   => 'text-blue-600 bg-blue-50 dark:bg-blue-500/10 border-blue-200/50 dark:border-blue-500/20',
                                'purple' => 'text-purple-600 bg-purple-50 dark:bg-purple-500/10 border-purple-200/50 dark:border-purple-500/20',
                                'green'  => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200/50 dark:border-emerald-500/20',
                                'red'    => 'text-red-600 bg-red-50 dark:bg-red-500/10 border-red-200/50 dark:border-red-500/20',
                                'zinc'   => 'text-zinc-600 bg-zinc-50 dark:bg-zinc-800 border-zinc-200/50 dark:border-zinc-700/50',
                            ];
                            $badgeClass = $colorMap[$current['color']] ?? $colorMap['zinc'];
                            $avatarColors = ['bg-blue-100 dark:bg-blue-500/15 text-blue-600', 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600', 'bg-purple-100 dark:bg-purple-500/15 text-purple-600', 'bg-orange-100 dark:bg-orange-500/15 text-orange-600', 'bg-rose-100 dark:bg-rose-500/15 text-rose-600'];
                            $avatarColor = $avatarColors[crc32($order->customer_name ?: 'X') % count($avatarColors)];
                        @endphp
                        <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-all duration-300">
                            <td class="pl-10 pr-6 py-5 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-black font-mono text-zinc-900 dark:text-zinc-100 group-hover:text-brand-primary transition-colors">{{ $order->order_number ?: 'CMD-'.str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-1">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-10 rounded-xl {{ $avatarColor }} flex items-center justify-center text-xs font-black shrink-0">
                                        {{ collect(explode(' ', $order->customer_name ?: 'X'))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200 leading-tight block truncate">{{ $order->customer_name ?: 'Client Inconnu' }}</span>
                                        <a href="tel:{{ $order->customer_phone }}" class="text-xs text-zinc-400 hover:text-brand-primary transition-colors mt-0.5 font-medium block">{{ $order->customer_phone }}</a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-zinc-100/50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-zinc-200/50 dark:border-zinc-700/50">
                                    <flux:icon.map-pin class="size-3 opacity-50" />
                                    {{ $order->city ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right whitespace-nowrap">
                                <span class="font-black text-zinc-900 dark:text-zinc-100 text-base">
                                    {{ number_format((float)$order->total, 0, ',', ' ') }} <span class="text-[10px] font-bold text-zinc-400">FCFA</span>
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <span class="size-2 rounded-full shrink-0 {{ ['orange' => 'bg-orange-500', 'blue' => 'bg-blue-500', 'purple' => 'bg-purple-500', 'green' => 'bg-emerald-500', 'red' => 'bg-rose-500', 'zinc' => 'bg-zinc-400'][$current['color']] ?? 'bg-zinc-400' }}"></span>
                                    <flux:select wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                                 class="!h-8 !text-[10px] !font-bold !rounded-xl max-w-[148px]"
                                                 value="{{ $order->status }}">
                                        @foreach(App\Models\Order::STATUSES as $key => $label)
                                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </td>
                            <td class="pl-6 pr-10 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-all transform sm:translate-x-2 sm:group-hover:translate-x-0">
                                    <flux:button href="{{ route('admin.orders.show', $order) }}" size="sm" variant="ghost" icon="eye" inset class="text-zinc-400 hover:text-brand-primary" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-10 py-32 text-center">
                                <div class="size-16 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <flux:icon.shopping-bag class="size-8 text-zinc-200 dark:text-zinc-700" />
                                </div>
                                <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucune commande trouvée</h3>
                                <p class="text-sm text-zinc-400 mt-2 max-w-xs mx-auto">Les commandes apparaîtront ici dès qu'un client passera commande.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->orders->hasPages())
            <div class="px-10 py-6 border-t border-zinc-100 dark:border-zinc-800/50 bg-zinc-50/10">
                {{ $this->orders->links() }}
            </div>
        @endif
    </div>
</div>
