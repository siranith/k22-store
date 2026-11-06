<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlySaleChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Monthly Sales & Benefit';
    
    // To make it take full width
    protected int | string | array $columnSpan = 2;

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $years = [];
        $currentYear = now()->year;
        for ($i = 0; $i < 5; $i++) {
            $years[(string) ($currentYear - $i)] = (string) ($currentYear - $i);
        }
        return $years;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $year = $activeFilter ? (int) $activeFilter : now()->year;

        // Get all sales for the selected year with eager loaded items and product
        $sales = Sale::with(['saleItems.product'])
            ->whereYear('created_at', $year)
            ->get();
        
        $salesData = [];
        $benefitData = [];
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach (range(1, 12) as $month) {
            $monthlySales = $sales->filter(function ($sale) use ($month) {
                return Carbon::parse($sale->created_at)->month === $month;
            });

            $monthlyTotal = $monthlySales->sum('total');
            $monthlyDiscount = $monthlySales->sum('discount');
            $monthlyDeliveryFee = $monthlySales->sum('delivery_fee');

            $monthlyCost = $monthlySales->flatMap(function ($sale) {
                return $sale->saleItems->map(function ($item) {
                    $cost = $item->product?->cost ?? 0;
                    $qty = $item->quantity ?? 0;
                    return $cost * $qty;
                });
            })->sum();

            // Calculate benefit excluding delivery fee (Total Sales - Cost - Discount - Delivery Fee)
            $monthlyBenefit = $monthlyTotal - $monthlyCost - $monthlyDiscount - $monthlyDeliveryFee;

            $salesData[] = round($monthlyTotal, 2);
            $benefitData[] = round($monthlyBenefit, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (' . $year . ')',
                    'data' => $salesData,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
                [
                    'label' => 'Benefit (' . $year . ')',
                    'data' => $benefitData,
                    'backgroundColor' => '#22C55E',
                    'borderColor' => '#16A34A',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
