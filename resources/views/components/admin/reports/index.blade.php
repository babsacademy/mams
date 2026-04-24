<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Rapports')] #[Layout('layouts.app')] class extends Component
{
    #[Url]
    public string $period = '30';

    public function updatedPeriod(): void
    {
        unset($this->stats, $this->topProducts, $this->salesByDay);
    }

    #[Computed]
    public function startDate(): Carbon|\DateTimeInterface
    {
        return match ($this->period) {
            '7'    => now()->subDays(7),
            '30'   => now()->subDays(30),
            '90'   => now()->subDays(90),
            'year' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }

    #[Computed]
    public function stats(): array
    {
        $orders = Order::where('created_at', '>=', $this->startDate)
            ->whereNotIn('status', ['cancelled']);

        $previousStart = $this->startDate->copy()->subDays((int) ($this->period === 'year' ? 365 : $this->period));
        $previousOrders = Order::whereBetween('created_at', [$previousStart, $this->startDate])
            ->whereNotIn('status', ['cancelled']);

        $revenue         = $orders->sum('total');
        $previousRevenue = $previousOrders->sum('total');
        $count           = $orders->count();
        $previousCount   = $previousOrders->count();

        $avgOrder         = $count > 0 ? $revenue / $count : 0;
        $previousAvg      = $previousCount > 0 ? $previousRevenue / $previousCount : 0;

        return [
            'revenue'         => $revenue,
            'revenue_trend'   => $previousRevenue > 0 ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0,
            'orders'          => $count,
            'orders_trend'    => $previousCount > 0 ? round((($count - $previousCount) / $previousCount) * 100, 1) : 0,
            'avg_order'       => $avgOrder,
            'avg_trend'       => $previousAvg > 0 ? round((($avgOrder - $previousAvg) / $previousAvg) * 100, 1) : 0,
            'delivered'       => Order::where('created_at', '>=', $this->startDate)->where('status', 'delivered')->count(),
        ];
    }

    #[Computed]
    public function topProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->whereHas('order', fn ($q) => $q->where('created_at', '>=', $this->startDate)->whereNotIn('status', ['cancelled']))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function salesByDay(): array
    {
        $days = (int) ($this->period === 'year' ? 365 : $this->period);
        $data = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('created_at', '>=', $this->startDate)
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'date'    => $date,
                'label'   => now()->subDays($i)->format('d/m'),
                'revenue' => $data->get($date)?->revenue ?? 0,
                'orders'  => $data->get($date)?->orders ?? 0,
            ];
        }

        return $result;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'rapports-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Commandes', 'Chiffre d\'affaires'], ';');

            foreach ($this->salesByDay as $row) {
                fputcsv($handle, [$row['date'], $row['orders'], $row['revenue']], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
};
?>

<div>
    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Rapports</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Analyse des performances de votre boutique</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:select wire:model.live="period" class="!rounded-xl !text-sm">
                <option value="7">7 derniers jours</option>
                <option value="30">30 derniers jours</option>
                <option value="90">90 derniers jours</option>
                <option value="year">Cette année</option>
            </flux:select>
            <flux:button wire:click="exportCsv" icon="arrow-down-tray" variant="ghost" size="sm" class="!rounded-xl">
                CSV
            </flux:button>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $symbol = config('app.currency_symbol', 'FCFA');
            $kpis = [
                ['label' => 'Chiffre d\'affaires', 'value' => number_format($this->stats['revenue'], 0, ',', ' ').' '.$symbol, 'trend' => $this->stats['revenue_trend'], 'icon' => 'banknotes'],
                ['label' => 'Commandes', 'value' => number_format($this->stats['orders']), 'trend' => $this->stats['orders_trend'], 'icon' => 'shopping-cart'],
                ['label' => 'Panier moyen', 'value' => number_format($this->stats['avg_order'], 0, ',', ' ').' '.$symbol, 'trend' => $this->stats['avg_trend'], 'icon' => 'receipt-percent'],
                ['label' => 'Livrées', 'value' => number_format($this->stats['delivered']), 'trend' => null, 'icon' => 'check-circle'],
            ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ $kpi['label'] }}</span>
                <flux:icon name="{{ $kpi['icon'] }}" class="size-4 text-zinc-400" />
            </div>
            <div class="text-xl font-black text-zinc-900 dark:text-white">{{ $kpi['value'] }}</div>
            @if($kpi['trend'] !== null)
            <div class="mt-1.5 text-xs font-semibold {{ $kpi['trend'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                {{ $kpi['trend'] >= 0 ? '▲' : '▼' }} {{ abs($kpi['trend']) }}% vs période précédente
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Top produits --}}
    <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-zinc-100 dark:border-zinc-800/50">
            <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider">Top produits</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                        <th class="text-left px-6 py-3 text-xs font-bold text-zinc-400 uppercase tracking-wider">#</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-zinc-400 uppercase tracking-wider">Produit</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-zinc-400 uppercase tracking-wider">Qté vendue</th>
                        <th class="text-right px-6 py-3 text-xs font-bold text-zinc-400 uppercase tracking-wider">Revenus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                    @forelse($this->topProducts as $i => $product)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="px-6 py-4 text-xs font-black text-zinc-400">{{ $i + 1 }}</td>
                        <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">{{ $product->product_name }}</td>
                        <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ number_format($product->total_qty) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-zinc-900 dark:text-white">{{ number_format($product->total_revenue, 0, ',', ' ') }} {{ $symbol }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-400 text-sm">
                            Aucune vente sur cette période.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
