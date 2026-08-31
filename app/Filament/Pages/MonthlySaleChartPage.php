<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\MonthlySaleChart;

class MonthlySaleChartPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'REPORT';
    protected static ?string $title = 'Monthly Sale Chart';

    protected static string $view = 'filament.pages.monthly-sale-chart-page';

    protected function getHeaderWidgets(): array
    {
        return [
            MonthlySaleChart::class,
        ];
    }
}
