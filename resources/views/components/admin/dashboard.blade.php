<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Title('Tableau de bord')] #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function totalOrders(): int
    {
        return Order::count();
    }

    #[Computed]
    public function pendingOrders(): int
    {
        return Order::where('status', 'pending')->count();
    }

    #[Computed]
    public function totalRevenue(): int
    {
        return Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])->sum('total');
    }

    #[Computed]
    public function totalProducts(): int
    {
        return Product::count();
    }

    #[Computed]
    public function totalCategories(): int
    {
        return Category::count();
    }

    #[Computed]
    public function recentOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with('items')->latest()->limit(5)->get();
    }

    #[Computed]
    public function lowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('stock', '<=', 5)->where('is_active', true)->limit(5)->get();
    }

    #[Computed]
    public function ordersTrend(): float
    {
        $today = Order::whereDate('created_at', today())->count();
        $yesterday = Order::whereDate('created_at', today()->subDay())->count();

        if ($yesterday === 0) {
            return $today > 0 ? 100.0 : 0.0;
        }

        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }

    #[Computed]
    public function revenueTrend(): float
    {
        $thisMonth = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $lastMonth = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');

        if ($lastMonth == 0) {
            return $thisMonth > 0 ? 100.0 : 0.0;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    #[Computed]
    public function sparklineOrders(): array
    {
        $counts = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => (int) ($counts[now()->subDays($daysAgo)->format('Y-m-d')] ?? 0))
            ->values()
            ->toArray();
    }

    #[Computed]
    public function sparklineRevenue(): array
    {
        $revenue = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => (int) ($revenue[now()->subDays($daysAgo)->format('Y-m-d')] ?? 0))
            ->values()
            ->toArray();
    }

    #[Computed]
    public function deliveredOrders(): int
    {
        return Order::where('status', 'delivered')->count();
    }

    #[Computed]
    public function avgOrderValue(): int
    {
        $total = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->totalRevenue / $total);
    }

    #[Computed]
    public function orderStatusBreakdown(): array
    {
        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $total = max(1, Order::count());

        $counts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return collect($statuses)->map(fn (string $status) => [
            'status' => $status,
            'count' => $counts[$status] ?? 0,
            'percent' => round((($counts[$status] ?? 0) / $total) * 100),
        ])->all();
    }

    #[Computed]
    public function topProductsByRevenue(): \Illuminate\Support\Collection
    {
        $rows = \App\Models\OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(price * quantity) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $maxRevenue = $rows->max('revenue') ?: 1;

        return $rows->map(fn ($row) => [
            'name' => $row->product_name,
            'qty' => $row->total_qty,
            'revenue' => $row->revenue,
            'percent' => round(($row->revenue / $maxRevenue) * 100),
        ]);
    }

    #[Computed]
    public function dailyOrdersChart(): array
    {
        $days = 30;
        $counts = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return collect(range($days - 1, 0))
            ->map(fn (int $d) => [
                'label' => now()->subDays($d)->format('d/m'),
                'value' => (int) ($counts[now()->subDays($d)->format('Y-m-d')] ?? 0),
            ])
            ->values()
            ->toArray();
    }

    #[Computed]
    public function dailyRevenueChart(): array
    {
        $days = 30;
        $revenue = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return collect(range($days - 1, 0))
            ->map(fn (int $d) => [
                'label' => now()->subDays($d)->format('d/m'),
                'value' => (int) ($revenue[now()->subDays($d)->format('Y-m-d')] ?? 0),
            ])
            ->values()
            ->toArray();
    }
}; ?>

@php
    function sparklinePoints(array $values): string
    {
        $maxVal = max(1, max($values));
        $count  = count($values);
        return collect($values)
            ->map(fn ($v, $i) => round($i * (100 / max(1, $count - 1)), 2) . ',' . round(32 - ($v / $maxVal) * 28, 2))
            ->implode(' ');
    }

    /**
     * @param  array<int, array{label: string, value: int}>  $data
     * @return array{line: string, area: string, points: list<array{x: float, y: float, label: string, value: int}>}
     */
    function buildAreaChart(array $data, float $w = 600, float $h = 100, float $pad = 8): array
    {
        $count = count($data);
        if ($count === 0) {
            return ['line' => '', 'area' => '', 'points' => []];
        }

        $values  = array_column($data, 'value');
        $maxVal  = max(1, max($values));
        $minX    = $pad;
        $maxX    = $w - $pad;
        $minY    = $pad;
        $maxY    = $h - $pad;

        $pts = [];
        foreach ($data as $i => $row) {
            $pts[] = [
                'x'     => $minX + ($count > 1 ? ($i / ($count - 1)) * ($maxX - $minX) : 0),
                'y'     => $maxY - ($row['value'] / $maxVal) * ($maxY - $minY),
                'label' => $row['label'],
                'value' => $row['value'],
            ];
        }

        if ($count === 1) {
            return [
                'line'   => "M {$pts[0]['x']} {$pts[0]['y']}",
                'area'   => "M {$pts[0]['x']} {$pts[0]['y']} L {$pts[0]['x']} {$maxY} Z",
                'points' => $pts,
            ];
        }

        $line = "M {$pts[0]['x']} {$pts[0]['y']}";
        for ($i = 1; $i < $count; $i++) {
            $prev = $pts[$i - 1];
            $curr = $pts[$i];
            $cpx  = ($prev['x'] + $curr['x']) / 2;
            $line .= " C {$cpx} {$prev['y']} {$cpx} {$curr['y']} {$curr['x']} {$curr['y']}";
        }

        $last = end($pts);
        $area = $line . " L {$last['x']} {$maxY} L {$pts[0]['x']} {$maxY} Z";

        return ['line' => $line, 'area' => $area, 'points' => $pts];
    }

    $statusConfig = [
        'pending'   => ['label' => 'En attente',  'dot' => 'bg-orange-500',  'bar' => 'bg-orange-400',  'text' => 'text-orange-600 dark:text-orange-400'],
        'confirmed' => ['label' => 'Confirmée',   'dot' => 'bg-blue-500',    'bar' => 'bg-blue-400',    'text' => 'text-blue-600 dark:text-blue-400'],
        'shipped'   => ['label' => 'Expédiée',    'dot' => 'bg-violet-500',  'bar' => 'bg-violet-400',  'text' => 'text-violet-600 dark:text-violet-400'],
        'delivered' => ['label' => 'Livrée',      'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-400', 'text' => 'text-emerald-600 dark:text-emerald-400'],
        'cancelled' => ['label' => 'Annulée',     'dot' => 'bg-zinc-400',    'bar' => 'bg-zinc-300',    'text' => 'text-zinc-500 dark:text-zinc-400'],
    ];

    $revenueChart = buildAreaChart($this->dailyRevenueChart, 600, 110, 6);
    $ordersChart  = buildAreaChart($this->dailyOrdersChart, 600, 110, 6);

    $ordersTrend  = $this->ordersTrend;
    $revenueTrend = $this->revenueTrend;
    $ordersPts    = sparklinePoints($this->sparklineOrders);
    $revenuePts   = sparklinePoints($this->sparklineRevenue);
    $activeProducts = Product::where('is_active', true)->count();
@endphp

<div class="max-w-7xl mx-auto pb-12 space-y-8">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Tableau de bord</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Bienvenue, {{ auth()->user()->name }}. Voici vos performances du {{ now()->translatedFormat('d F Y') }}.
            </p>
        </div>
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary/90 rounded-xl transition-all shadow-sm shrink-0">
            <flux:icon.plus class="size-4" />
            Nouveau produit
        </a>
    </div>

    {{-- ── KPI Cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">

        {{-- Commandes --}}
        <div class="group relative overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 border-l-2 border-l-brand-primary rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Commandes</span>
                <flux:icon.shopping-bag class="size-4 text-brand-primary/50" stroke-width="1.5" />
            </div>
            <div class="flex items-baseline gap-2">
                <p class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight">{{ number_format($this->totalOrders) }}</p>
                @if($ordersTrend != 0)
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $ordersTrend > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        @if($ordersTrend > 0)<flux:icon.arrow-up-right class="size-3" />@else<flux:icon.arrow-down-right class="size-3" />@endif
                        {{ abs($ordersTrend) }}%
                    </span>
                @endif
            </div>
            @if($this->pendingOrders > 0)
                <div class="mt-3 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="relative flex size-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full size-2 bg-orange-500"></span>
                    </span>
                    {{ $this->pendingOrders }} en attente
                </div>
            @else
                <div class="mt-3 h-4"></div>
            @endif
            <div class="absolute bottom-0 left-0 right-0 h-10 opacity-20">
                <svg viewBox="0 0 100 32" class="w-full h-full text-brand-primary" preserveAspectRatio="none">
                    <polyline points="{{ $ordersPts }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Revenus --}}
        <div class="group relative overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 border-l-2 border-l-brand-primary rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Chiffre d'affaires</span>
                <flux:icon.banknotes class="size-4 text-brand-primary/50" stroke-width="1.5" />
            </div>
            <div class="flex items-baseline gap-2 flex-wrap">
                <p class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                    {{ number_format($this->totalRevenue, 0, ',', ' ') }}
                </p>
                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500">FCFA</span>
            </div>
            <div class="mt-3 flex items-center gap-2">
                @if($revenueTrend != 0)
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $revenueTrend > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        @if($revenueTrend > 0)<flux:icon.arrow-up-right class="size-3" />@else<flux:icon.arrow-down-right class="size-3" />@endif
                        {{ abs($revenueTrend) }}%
                    </span>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">vs mois préc.</span>
                @else
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">Confirmés uniquement</span>
                @endif
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-10 opacity-20">
                <svg viewBox="0 0 100 32" class="w-full h-full text-brand-primary" preserveAspectRatio="none">
                    <polyline points="{{ $revenuePts }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Valeur moyenne --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Panier moyen</span>
                <flux:icon.calculator class="size-4 text-zinc-400 dark:text-zinc-500" stroke-width="1.5" />
            </div>
            <div class="flex items-baseline gap-2">
                <p class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ number_format($this->avgOrderValue, 0, ',', ' ') }}
                </p>
                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500">FCFA</span>
            </div>
            <div class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                Sur {{ $this->deliveredOrders > 0 ? $this->deliveredOrders.' livraison'.($this->deliveredOrders > 1 ? 's' : '') : 'les commandes confirmées' }}
            </div>
        </div>

        {{-- Produits --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Catalogue</span>
                <flux:icon.cube class="size-4 text-zinc-400 dark:text-zinc-500" stroke-width="1.5" />
            </div>
            <div class="flex items-baseline gap-2">
                <p class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight">{{ $this->totalProducts }}</p>
            </div>
            <div class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $activeProducts }}</span> actifs
                · <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->totalCategories }}</span> collections
            </div>
        </div>
    </div>

    {{-- ── Charts Row ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800/50">
                <div>
                    <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <span class="w-3 h-0.5 bg-brand-primary inline-block shrink-0"></span>
                        Revenus — 30 derniers jours
                    </h2>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">Commandes confirmées, expédiées et livrées</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-zinc-400 dark:text-zinc-500 hover:text-brand-primary transition-colors">
                    Voir tout →
                </a>
            </div>
            <div class="p-4 pt-5">
                <svg viewBox="0 0 600 110" class="w-full" preserveAspectRatio="none" style="height:110px;">
                    <defs>
                        <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(236,72,153)" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="rgb(236,72,153)" stop-opacity="0.01" />
                        </linearGradient>
                    </defs>
                    @if($revenueChart['area'])
                        <path d="{{ $revenueChart['area'] }}" fill="url(#revGrad)" />
                        <path d="{{ $revenueChart['line'] }}" fill="none" stroke="rgb(236,72,153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        {{-- Dots on non-zero values --}}
                        @foreach($revenueChart['points'] as $pt)
                            @if($pt['value'] > 0)
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3" fill="rgb(236,72,153)" />
                            @endif
                        @endforeach
                    @else
                        <text x="300" y="60" text-anchor="middle" class="fill-zinc-400" font-size="12">Aucune donnée</text>
                    @endif
                </svg>
                {{-- X axis labels: first, middle, last --}}
                @php
                    $chartData = $this->dailyRevenueChart;
                    $chartCount = count($chartData);
                @endphp
                @if($chartCount > 0)
                    <div class="flex justify-between mt-1 px-1">
                        <span class="text-[10px] text-zinc-400 dark:text-zinc-500">{{ $chartData[0]['label'] }}</span>
                        @if($chartCount > 2)
                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500">{{ $chartData[(int)floor($chartCount / 2)]['label'] }}</span>
                        @endif
                        <span class="text-[10px] text-zinc-400 dark:text-zinc-500">{{ $chartData[$chartCount - 1]['label'] }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Order Status Breakdown --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-zinc-100 dark:border-zinc-800/50">
                <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <span class="w-3 h-0.5 bg-brand-primary inline-block shrink-0"></span>
                    Statuts commandes
                </h2>
                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">Répartition par statut</p>
            </div>
            <div class="p-6 space-y-4">
                @foreach($this->orderStatusBreakdown as $item)
                    @php $sc = $statusConfig[$item['status']] ?? ['label' => ucfirst($item['status']), 'dot' => 'bg-zinc-400', 'bar' => 'bg-zinc-300', 'text' => 'text-zinc-500']; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="size-1.5 rounded-full {{ $sc['dot'] }} shrink-0"></span>
                                <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $sc['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-zinc-900 dark:text-white">{{ $item['count'] }}</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 w-7 text-right">{{ $item['percent'] }}%</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full {{ $sc['bar'] }} rounded-full transition-all duration-700"
                                 style="width: {{ $item['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Content Grid ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Dernières commandes --}}
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800/50">
                <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <span class="w-3 h-0.5 bg-brand-primary inline-block shrink-0"></span>
                    Commandes récentes
                </h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-zinc-400 dark:text-zinc-500 hover:text-brand-primary dark:hover:text-brand-primary transition-colors">Voir tout →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wider">Commande</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wider">Client</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wider">Statut</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                    @forelse($this->recentOrders as $order)
                        @php $sc = $statusConfig[$order->status] ?? ['label' => ucfirst($order->status), 'dot' => 'bg-zinc-500', 'text' => 'text-zinc-500']; @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors group">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-zinc-900 dark:text-white group-hover:text-brand-primary transition-colors text-xs">{{ $order->order_number }}</span>
                                    <span class="text-[11px] text-zinc-400 dark:text-zinc-500">{{ $order->created_at->format('d/m/Y') }}</span>
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-7 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] font-black text-zinc-600 dark:text-zinc-300 shrink-0">
                                        {{ collect(explode(' ', $order->customer_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                                    </div>
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $order->customer_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $sc['text'] ?? 'text-zinc-500' }}">
                                    <span class="size-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="font-bold text-zinc-900 dark:text-white text-sm">{{ number_format($order->total, 0, ',', ' ') }}</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 ml-0.5">FCFA</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <flux:icon.inbox class="size-8 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" stroke-width="1" />
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Aucune commande</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Les nouvelles commandes apparaîtront ici.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-5">

            {{-- Top Products --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800/50">
                    <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <span class="w-3 h-0.5 bg-brand-primary inline-block shrink-0"></span>
                        Top produits
                    </h2>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">Par chiffre d'affaires généré</p>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($this->topProductsByRevenue as $i => $product)
                        <div class="px-5 py-3.5">
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="text-[10px] font-black text-zinc-300 dark:text-zinc-600 w-4 shrink-0">#{{ $i + 1 }}</span>
                                    <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $product['name'] }}</span>
                                </div>
                                <span class="text-xs font-black text-zinc-900 dark:text-white shrink-0">{{ number_format($product['revenue'], 0, ',', ' ') }} <span class="font-normal text-zinc-400 text-[10px]">FCFA</span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-primary rounded-full" style="width: {{ $product['percent'] }}%"></div>
                                </div>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 shrink-0">{{ $product['qty'] }} vte{{ $product['qty'] > 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <flux:icon.chart-bar class="size-7 mx-auto text-zinc-300 dark:text-zinc-600 mb-2" stroke-width="1" />
                            <p class="text-xs text-zinc-400 dark:text-zinc-500">Aucune vente enregistrée</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Stock Alerts --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800/50">
                    <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <span class="w-3 h-0.5 bg-orange-400 inline-block shrink-0"></span>
                        Alertes stock
                    </h2>
                </div>
                @if($this->lowStockProducts->count() > 0)
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                        @foreach($this->lowStockProducts as $product)
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="group flex items-center gap-3 px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <div class="size-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 overflow-hidden shrink-0">
                                    @if($product->image_url)
                                        @php
                                            $productImageUrl = Str::startsWith($product->image_url, ['http://', 'https://', 'data:', '/'])
                                                ? $product->image_url
                                                : asset('storage/'.ltrim($product->image_url, '/'));
                                        @endphp
                                        <img src="{{ $productImageUrl }}"
                                             alt="{{ $product->name }}"
                                             class="size-full object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-all" loading="lazy" />
                                    @else
                                        <div class="size-full flex items-center justify-center">
                                            <flux:icon.photo class="size-4 text-zinc-300 dark:text-zinc-600" />
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate">{{ $product->name }}</p>
                                    @if($product->stock === 0)
                                        <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium mt-0.5">Rupture de stock</p>
                                    @else
                                        <p class="text-[11px] text-orange-600 dark:text-orange-400 font-medium mt-0.5">{{ $product->stock }} restant{{ $product->stock > 1 ? 's' : '' }}</p>
                                    @endif
                                </div>
                                <flux:icon.arrow-right class="size-3.5 text-zinc-300 dark:text-zinc-600 group-hover:text-brand-primary transition-colors shrink-0" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-10 text-center">
                        <flux:icon.check-badge class="size-8 mx-auto text-emerald-400 dark:text-emerald-500 mb-2" stroke-width="1.5" />
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Stocks optimaux</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Aucun réapprovisionnement urgent.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
