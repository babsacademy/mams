<?php

use App\Actions\UpdateOrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Détail commande')] #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public string $status;

    public function mount(Order $order): void
    {
        $this->order  = $order->load('items.product');
        $this->status = $order->status;
    }

    public function updateStatus(): void
    {
        Gate::authorize('admin-action');

        (new UpdateOrderStatus)->execute($this->order, $this->status);
        $this->order->refresh();
        $this->dispatch('notify', message: 'Statut mis à jour.');
    }
}; ?>

<div>
    <div class="mb-10 flex items-center justify-between">
        <div class="flex items-center gap-5">
            <flux:button href="{{ route('admin.orders.index') }}" variant="ghost" icon="arrow-left" inset class="text-zinc-400 hover:text-white" />
            <div>
                <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $order->order_number }}</h1>
                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Passée le {{ $order->created_at->translatedFormat('d F Y \à H:i') }}</p>
            </div>
        </div>
        
        <div class="hidden sm:flex items-center gap-2">
            @php
                $statusColors = [
                    'pending'   => 'text-orange-600 bg-orange-50 dark:bg-orange-500/10',
                    'confirmed' => 'text-blue-600 bg-blue-50 dark:bg-blue-500/10',
                    'shipped'   => 'text-purple-600 bg-purple-50 dark:bg-purple-500/10',
                    'delivered' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10',
                    'cancelled' => 'text-rose-600 bg-rose-50 dark:bg-rose-500/10',
                ];
                $sc = $statusColors[$order->status] ?? 'text-zinc-600 bg-zinc-50 dark:bg-zinc-800';
            @endphp
            <span class="px-4 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-full border border-current/20 {{ $sc }}">
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    {{-- ── Status Timeline ──────────────────────────────────────── --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm px-8 py-6 mb-8">
        @php
            $allSteps       = ['pending', 'confirmed', 'shipped', 'delivered'];
            $isCancelled    = $order->status === 'cancelled';
            $currentStepIdx = array_search($order->status, $allSteps);
            $stepLabels     = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'shipped' => 'Expédiée', 'delivered' => 'Livrée'];
        @endphp

        @if($isCancelled)
            <div class="flex items-center gap-4">
                <div class="size-9 rounded-full bg-rose-100 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 flex items-center justify-center shrink-0">
                    <flux:icon.x-mark class="size-4 text-rose-500" stroke-width="2.5" />
                </div>
                <div>
                    <p class="text-sm font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">Commande annulée</p>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $order->updated_at->translatedFormat('d F Y \à H:i') }}</p>
                </div>
            </div>
        @else
            <div class="flex items-center">
                @foreach($allSteps as $i => $step)
                    @php
                        $isCompleted = $currentStepIdx !== false && $i <= $currentStepIdx;
                        $isCurrent   = $currentStepIdx !== false && $i === $currentStepIdx;
                    @endphp
                    <div class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center gap-1.5 shrink-0">
                            <div class="size-9 rounded-full border-2 flex items-center justify-center transition-all
                                {{ $isCompleted ? 'bg-brand-primary border-brand-primary shadow-sm shadow-brand-primary/20' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700' }}">
                                @if($step === 'pending')
                                    <flux:icon.clock class="size-4 {{ $isCompleted ? 'text-white' : 'text-zinc-300 dark:text-zinc-600' }}" stroke-width="2" />
                                @elseif($step === 'confirmed')
                                    <flux:icon.check-badge class="size-4 {{ $isCompleted ? 'text-white' : 'text-zinc-300 dark:text-zinc-600' }}" stroke-width="2" />
                                @elseif($step === 'shipped')
                                    <flux:icon.truck class="size-4 {{ $isCompleted ? 'text-white' : 'text-zinc-300 dark:text-zinc-600' }}" stroke-width="2" />
                                @elseif($step === 'delivered')
                                    <flux:icon.check-circle class="size-4 {{ $isCompleted ? 'text-white' : 'text-zinc-300 dark:text-zinc-600' }}" stroke-width="2" />
                                @endif
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest whitespace-nowrap
                                {{ $isCurrent ? 'text-brand-primary' : ($isCompleted ? 'text-zinc-600 dark:text-zinc-400' : 'text-zinc-300 dark:text-zinc-600') }}">
                                {{ $stepLabels[$step] }}
                            </span>
                        </div>
                        @if(! $loop->last)
                            <div class="flex-1 h-0.5 mx-3 mb-5 rounded-full {{ $currentStepIdx !== false && $i < $currentStepIdx ? 'bg-brand-primary' : 'bg-zinc-200 dark:bg-zinc-800' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            {{-- Articles --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-zinc-100 dark:border-zinc-800/50 flex items-center justify-between">
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Panier client</h2>
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">{{ $order->items->count() }} {{ Str::plural('article', $order->items->count()) }}</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                                <th class="text-left pl-10 pr-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Produit</th>
                                <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Qté</th>
                                <th class="text-right px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Unitaire</th>
                                <th class="text-right pl-6 pr-10 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                            @foreach($order->items as $item)
                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="pl-10 pr-6 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="relative size-14 rounded-xl overflow-hidden shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                                            @if($item->product?->image_url)
                                                @php
                                                    $productImageUrl = Str::startsWith($item->product->image_url, ['http://', 'https://', 'data:', '/'])
                                                        ? $item->product->image_url
                                                        : asset('storage/'.ltrim($item->product->image_url, '/'));
                                                @endphp
                                                <img src="{{ $productImageUrl }}" alt="{{ $item->product_name }}" class="absolute inset-0 w-full h-full object-cover">
                                            @else
                                                <div class="size-full flex items-center justify-center">
                                                    <flux:icon.photo class="size-6 text-zinc-300 opacity-50" />
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-primary transition-colors">{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-center whitespace-nowrap font-black text-zinc-500 dark:text-zinc-400">x{{ $item->quantity }}</td>
                                <td class="px-6 py-6 text-right text-zinc-600 dark:text-zinc-400 font-medium whitespace-nowrap">{{ number_format($item->price, 0, ',', ' ') }} <span class="text-[10px]">FCFA</span></td>
                                <td class="pl-6 pr-10 py-6 text-right font-black text-zinc-900 dark:text-zinc-100 whitespace-nowrap">{{ number_format($item->subtotal, 0, ',', ' ') }} <span class="text-[10px] text-zinc-400">FCFA</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50/30 dark:bg-zinc-800/30 border-t border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <td colspan="3" class="pl-10 pr-6 py-8 text-right font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest text-[11px]">Prix total</td>
                                <td class="pl-6 pr-10 py-8 text-right font-black text-3xl text-zinc-900 dark:text-white whitespace-nowrap">
                                    {{ number_format($order->total, 0, ',', ' ') }} <span class="text-sm font-bold text-zinc-400">FCFA</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            @if($order->notes)
                <div class="bg-amber-50/50 dark:bg-amber-500/5 rounded-2xl border border-amber-200/50 dark:border-amber-500/20 p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <flux:icon.chat-bubble-bottom-center-text class="size-5 text-amber-600" />
                        <h3 class="text-base font-black text-amber-800 dark:text-amber-500 uppercase tracking-tight">Notes client</h3>
                    </div>
                    <p class="text-sm text-amber-900/80 dark:text-amber-400/80 italic leading-relaxed">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-8">
            {{-- Client --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-6">
                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Infos Client</h3>
                
                <div class="space-y-5">
                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.user class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Nom complet</p>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $order->customer_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.phone class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Numéro de téléphone</p>
                            <a href="tel:{{ $order->customer_phone }}" class="font-bold text-zinc-900 dark:text-white hover:text-brand-primary transition-colors underline decoration-zinc-200 dark:decoration-zinc-800 underline-offset-4">
                                {{ $order->customer_phone }}
                            </a>
                        </div>
                    </div>

                    @if($order->customer_email)
                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.envelope class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Addresse Email</p>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $order->customer_email }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.map-pin class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Destination</p>
                            <p class="font-bold text-zinc-900 dark:text-white leading-snug">{{ $order->customer_address }}, {{ $order->city }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statut --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-6">
                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Mise à jour statut</h3>
                
                <div class="space-y-4">
                    <flux:select wire:model="status" class="!h-12 !font-bold">
                        @foreach(App\Models\Order::STATUSES as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:button wire:click="updateStatus" variant="primary" class="w-full !h-12 font-black uppercase tracking-widest !bg-brand-primary border-none hover:shadow-brand-primary/30 shadow-lg">
                        Confirmer le statut
                    </flux:button>
                </div>
            </div>

            {{-- WhatsApp --}}
            <a href="https://wa.me/{{ $order->customer_phone }}?text={{ urlencode('Bonjour '.$order->customer_name.', votre commande '.$order->order_number.' est en cours de traitement.') }}"
               target="_blank"
               class="flex items-center justify-center gap-3 w-full bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-500 border border-emerald-500/20 font-black uppercase tracking-widest py-4 rounded-2xl transition hover:shadow-lg shadow-emerald-500/5 text-xs">
                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.661-1.597-.91-2.204-.242-.587-.482-.508-.661-.517-.17-.008-.364-.01-.557-.01s-.507.072-.772.359c-.265.287-1.011.987-1.011 2.407s1.035 2.797 1.18 3.01c.145.213 2.035 3.108 4.93 4.358.688.297 1.226.474 1.646.608.691.22 1.32.19 1.817.115.553-.083 1.758-.718 2.007-1.412.249-.694.249-1.289.175-1.412-.074-.123-.274-.197-.571-.347zM12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.118 1.511 5.86L0 24l6.335-1.662C7.94 23.273 9.893 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.898 0-3.664-.52-5.176-1.425l-.371-.221-3.845 1.009 1.026-3.747-.242-.385C2.474 15.719 2 13.916 2 12c0-5.514 4.486-10 10-10s10 4.486 10 10-4.486 10-10 10z"/></svg>
                Ouvrir WhatsApp
            </a>
        </div>
    </div>
</div>
