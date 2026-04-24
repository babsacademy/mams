# -*- coding: utf-8 -*-
import os

filepath = r"c:\dev\schic\resources\views\components\admin\orders\⚡show.blade.php"

content = '''<?php

use App\\Models\\Order;
use Livewire\\Attributes\\Layout;
use Livewire\\Attributes\\Title;
use Livewire\\Component;

new #[Title('D\u00e9tail commande')] #[Layout('layouts.app')] class extends Component
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
        if (! array_key_exists($this->status, Order::STATUSES)) {
            return;
        }

        $this->order->update(['status' => $this->status]);
        $this->dispatch('notify', message: 'Statut mis \u00e0 jour.');
    }
}; ?>

<div>
    {{-- Page Header with breadcrumb feel --}}
    <div class="mb-10 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('admin.orders.index') }}" variant="ghost" icon="arrow-left" inset class="text-zinc-400 hover:text-white">
                Retour aux commandes
            </flux:button>
            <div class="h-8 w-px bg-zinc-200 dark:bg-zinc-700 hidden sm:block"></div>
            <div class="hidden sm:block">
                <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $order->order_number }}</h1>
                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Pass\u00e9e le {{ $order->created_at->translatedFormat('d F Y \\\\\\u00e0 H:i') }}</p>
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
            <span class="px-4 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-2xl border border-current/20 {{ $sc }}">
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    {{-- Mobile: order number + status below back button --}}
    <div class="sm:hidden mb-8">
        <h1 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $order->order_number }}</h1>
        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Pass\u00e9e le {{ $order->created_at->translatedFormat('d F Y \\\\\\u00e0 H:i') }}</p>
        <div class="mt-3">
            @php
                $statusColors = $statusColors ?? [
                    'pending'   => 'text-orange-600 bg-orange-50 dark:bg-orange-500/10',
                    'confirmed' => 'text-blue-600 bg-blue-50 dark:bg-blue-500/10',
                    'shipped'   => 'text-purple-600 bg-purple-50 dark:bg-purple-500/10',
                    'delivered' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10',
                    'cancelled' => 'text-rose-600 bg-rose-50 dark:bg-rose-500/10',
                ];
                $sc = $sc ?? ($statusColors[$order->status] ?? 'text-zinc-600 bg-zinc-50 dark:bg-zinc-800');
            @endphp
            <span class="px-4 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-2xl border border-current/20 {{ $sc }}">
                {{ $order->status_label }}
            </span>
        </div>
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
                                <th class="text-left pl-8 pr-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Produit</th>
                                <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Qt\u00e9</th>
                                <th class="text-right px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Unitaire</th>
                                <th class="text-right pl-6 pr-8 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors border-b border-zinc-100 dark:border-zinc-800/30 last:border-b-0">
                                <td class="pl-8 pr-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-hidden shrink-0">
                                            @if($item->product?->image_url)
                                                <img src="{{ Str::startsWith($item->product->image_url, ['http://', 'https://']) ? $item->product->image_url : asset('storage/' . $item->product->image_url) }}" alt="{{ $item->product_name }}" class="size-full object-cover">
                                            @else
                                                <div class="size-full flex items-center justify-center">
                                                    <flux:icon.photo class="size-5 text-zinc-300 opacity-50" />
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-pink transition-colors">{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center whitespace-nowrap font-black text-zinc-500 dark:text-zinc-400">x{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-right text-zinc-600 dark:text-zinc-400 font-medium whitespace-nowrap">{{ number_format($item->price, 0, ',', ' ') }} <span class="text-[10px]">FCFA</span></td>
                                <td class="pl-6 pr-8 py-5 text-right font-black text-zinc-900 dark:text-zinc-100 whitespace-nowrap">{{ number_format($item->subtotal, 0, ',', ' ') }} <span class="text-[10px] text-zinc-400">FCFA</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-zinc-200 dark:border-zinc-700">
                                <td colspan="3" class="pl-8 pr-6 py-6 text-right font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest text-[11px]">Prix total</td>
                                <td class="pl-6 pr-8 py-6 text-right font-black text-2xl text-zinc-900 dark:text-white whitespace-nowrap">
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
                        <div class="size-9 rounded-xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                            <flux:icon.chat-bubble-bottom-center-text class="size-4 text-amber-600" />
                        </div>
                        <h3 class="text-base font-black text-amber-800 dark:text-amber-500 uppercase tracking-tight">Notes client</h3>
                    </div>
                    <p class="text-sm text-amber-900/80 dark:text-amber-400/80 italic leading-relaxed pl-12">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-8">
            {{-- Client --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8">
                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-6">Infos Client</h3>

                <div class="space-y-0 divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    <div class="flex items-start gap-4 pb-5">
                        <div class="size-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.user class="size-4 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Nom complet</p>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $order->customer_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 py-5">
                        <div class="size-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.phone class="size-4 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Num\u00e9ro de t\u00e9l\u00e9phone</p>
                            <a href="tel:{{ $order->customer_phone }}" class="font-bold text-zinc-900 dark:text-white hover:text-brand-pink transition-colors underline decoration-zinc-200 dark:decoration-zinc-800 underline-offset-4">
                                {{ $order->customer_phone }}
                            </a>
                        </div>
                    </div>

                    @if($order->customer_email)
                    <div class="flex items-start gap-4 py-5">
                        <div class="size-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.envelope class="size-4 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Adresse Email</p>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $order->customer_email }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-start gap-4 pt-5">
                        <div class="size-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <flux:icon.map-pin class="size-4 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-black">Destination</p>
                            <p class="font-bold text-zinc-900 dark:text-white leading-snug">{{ $order->customer_address }}, {{ $order->city }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statut --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8">
                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-6">Mise \u00e0 jour statut</h3>

                <div class="space-y-4">
                    <flux:select wire:model="status" class="!h-12 !font-bold">
                        @foreach(App\\Models\\Order::STATUSES as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:button wire:click="updateStatus" variant="primary" class="w-full !h-12 font-black uppercase tracking-widest !bg-brand-pink border-none hover:shadow-brand-pink/30 shadow-lg !rounded-2xl">
                        Confirmer le statut
                    </flux:button>
                </div>
            </div>

            {{-- WhatsApp --}}
            <a href="https://wa.me/{{ $order->customer_phone }}?text={{ urlencode('Bonjour '.$order->customer_name.', votre commande '.$order->order_number.' est en cours de traitement.') }}"
               target="_blank"
               class="flex items-center justify-center gap-3 w-full bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-500 border border-emerald-500/20 font-black uppercase tracking-widest py-4 rounded-2xl transition hover:shadow-lg shadow-emerald-500/5 text-xs">
                <div class="size-9 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.661-1.597-.91-2.204-.242-.587-.482-.508-.661-.517-.17-.008-.364-.01-.557-.01s-.507.072-.772.359c-.265.287-1.011.987-1.011 2.407s1.035 2.797 1.18 3.01c.145.213 2.035 3.108 4.93 4.358.688.297 1.226.474 1.646.608.691.22 1.32.19 1.817.115.553-.083 1.758-.718 2.007-1.412.249-.694.249-1.289.175-1.412-.074-.123-.274-.197-.571-.347zM12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.118 1.511 5.86L0 24l6.335-1.662C7.94 23.273 9.893 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.898 0-3.664-.52-5.176-1.425l-.371-.221-3.845 1.009 1.026-3.747-.242-.385C2.474 15.719 2 13.916 2 12c0-5.514 4.486-10 10-10s10 4.486 10 10-4.486 10-10 10z"/></svg>
                </div>
                Ouvrir WhatsApp
            </a>
        </div>
    </div>
</div>
'''

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("File written successfully.")
