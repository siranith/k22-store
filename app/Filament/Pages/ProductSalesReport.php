<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSalesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'POS';
    protected static ?string $navigationLabel = 'Product Sales Report';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.product-sales-report';

    public $rows = [];
    public $from;
    public $until;
    public $period;
    public $search;
    public $totalQuantity = 0;
    public $totalRevenue = 0;

    public function mount(Request $request)
    {
        $this->period = $request->input('period', 'day');
        $this->from = $request->input('from');
        $this->until = $request->input('until');
        $this->search = $request->input('search', '');

        if ($this->period === 'day') {
            $this->from = $this->from ?: Carbon::today()->toDateString();
            $this->until = $this->until ?: Carbon::today()->toDateString();
        } elseif ($this->period === 'week') {
            $this->from = $this->from ?: Carbon::now()->startOfWeek()->toDateString();
            $this->until = $this->until ?: Carbon::now()->endOfWeek()->toDateString();
        } elseif ($this->period === 'month') {
            $this->from = $this->from ?: Carbon::now()->startOfMonth()->toDateString();
            $this->until = $this->until ?: Carbon::now()->endOfMonth()->toDateString();
        } else {
            $this->from = $this->from ?: Carbon::today()->toDateString();
            $this->until = $this->until ?: Carbon::today()->toDateString();
        }

        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', '>=', $this->from)
            ->whereDate('sales.created_at', '<=', $this->until);

        if ($this->search) {
            $query->where('products.name', 'like', '%' . $this->search . '%');
        }

        $this->rows = $query->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku as sku',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->get();

        $this->totalQuantity = $this->rows->sum('total_quantity');
        $this->totalRevenue = $this->rows->sum('total_revenue');
    }
}
