<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlySaleChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Monthly Sales';
    
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

        // Get all sales for the selected year
        $sales = Sale::whereYear('created_at', $year)->get();
        
        $data = [];
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach (range(1, 12) as $month) {
            $data[] = $sales->filter(function ($sale) use ($month) {
                return Carbon::parse($sale->created_at)->month === $month;
            })->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (' . $year . ')',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
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
