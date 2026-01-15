<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\getEloquent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SalesSummaryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null; // Disable auto-refresh

        // protected int | string | array $columnSpan = [
        //         'default' => 2,
        //         'md' => 2,
        //         'xl' => 2,
        // ];
    protected int | string | array $columnSpan = 'full';
        // Show stats in 3 columns inside the widget
        protected int $columns = 3;
    protected function getStats(): array
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Sum of 'paid' for different time frames
        $dailyPaid = Sale::whereDate('created_at', $today)->sum('paid');
        $weeklyPaid = Sale::whereBetween('created_at', [$startOfWeek, Carbon::now()])->sum('paid');
        $monthlyPaid = Sale::whereBetween('created_at', [$startOfMonth, Carbon::now()])->sum('paid');

        $countDaily = Sale::whereDate('created_at', $today)->count();
        $count1Week = Sale::where('created_at', '>=', $now->copy()->subWeek())->count();
        $count1Month  = Sale::where('created_at', '>=', $now->copy()->subMonths(1))->count();
        $count1Year   = Sale::where('created_at', '>=', $now->copy()->subYear())->count();


        $Benefit = SaleItem::whereHas('sale', function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            })
            ->with('product') // eager load product for cost
            ->get()
            ->sum(function ($item) {
                $cost = $item->product?->cost ?? 0;
                return ($item->unit_price - $cost) * $item->quantity;
            });
        $dailyBenefit = $Benefit - Sale::whereDate('created_at', $today)->sum('discount');

        // Top selling product by quantity
        $top = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->first();

        $topProductName = $top?->name ?? '—';
        $topProductQty = $top?->total_qty ?? 0;

        return [
            Stat::make('Daily Sales', number_format($dailyPaid, 2) . ' $')
                ->description('Total sale today')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),
            Stat::make('Daily Profit(after discount)', number_format($dailyBenefit, 2) . ' $')
                ->description('Total profit today')
                ->color('primary'),
            Stat::make('Weekly Sales', number_format($weeklyPaid, 2) . ' $')
                ->description('Total sale this week')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
            Stat::make('Monthly Sales', number_format($monthlyPaid, 2) . ' $')
                ->description('Total sale this month')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),

            // 🔢 Transaction counts
            Stat::make('Transactions (Daily)', $countDaily)
                ->description('Today')
                ->icon('heroicon-o-receipt-refund')
                ->color('warning'),
            Stat::make('Transactions (1 Week)', $count1Week)
                ->description('Last 7 days')
                ->icon('heroicon-o-receipt-refund')
                ->color('success'),

            Stat::make('Transactions (1 Months)', $count1Month)
                ->description('Last 1 months')
                ->icon('heroicon-o-receipt-refund')
                ->color('info'),

            Stat::make('Transactions (1 Year)', $count1Year)
                ->description('Last 12 months')
                ->icon('heroicon-o-receipt-refund')
                ->color('primary'),

            Stat::make('Top Product', $topProductName . ' — ' . $topProductQty . ' units')
                ->description('Top selling product by quantity')
                ->icon('heroicon-o-chart-bar')
                ->color('secondary'),

        ];
    }
}
