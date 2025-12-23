<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\getEloquent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SalesSummaryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null; // Disable auto-refresh

      protected int | string | array $columnSpan = [
        'default' => 2,
        'md' => 2,
        'xl' => 2,
    ];
    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Sum of 'paid' for different time frames
        $dailyPaid = Sale::whereDate('created_at', $today)->sum('paid');
        $weeklyPaid = Sale::whereBetween('created_at', [$startOfWeek, Carbon::now()])->sum('paid');
        $monthlyPaid = Sale::whereBetween('created_at', [$startOfMonth, Carbon::now()])->sum('paid');

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

        return [
            Stat::make('Daily Sales', number_format($dailyPaid, 2) . ' $')
                ->description('Total sale today')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Weekly Sales', number_format($weeklyPaid, 2) . ' $')
                ->description('Total sale this week')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Monthly Sales', number_format($monthlyPaid, 2) . ' $')
                ->description('Total sale this month')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
            Stat::make('Daily Profit(after discount)', number_format($dailyBenefit, 2) . ' $')
                ->description('Total profit today')
                ->color('primary'),

        ];
    }
}
