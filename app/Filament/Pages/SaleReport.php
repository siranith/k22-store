<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;
    protected static string $view = 'filament.resources.sale-report-resource.pages.sale-report';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'POS';
    protected static ?string $navigationLabel = 'Sale Report';
    protected static ?string $slug = 'sale-report';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 3;
    // protected static string $view = 'filament.pages.sale-report';

    public float $totalPaid = 0;
    public float $totalBenefit = 0;
    public float $totalDiscount = 0;

    protected function getViewData(): array
    {
        // Calculate total paid and total benefit
        $query = Sale::query()
            ->with(['saleItems.product'])
            ->when($this->tableFilters['date_range']['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($this->tableFilters['date_range']['until'] ?? null, fn ($q, $until) => $q->whereDate('created_at', '<=', $until));

        $sales = $query->get();

        $this->total = $sales->sum('total');
        $this->totalPaid = $sales->sum('paid');
        $this->totalDiscount = $sales->sum('discount');
        $this->totalCost = $sales->flatMap(function ($sale) {
            return $sale->saleItems->map(function ($item) {
                $cost = $item->product->cost ?? 0;
                $qty = $item->quantity ?? 0;
                return $cost * $qty;
            });
        })->sum();
        $this->totalBenefit = $sales->sum('total') - $this->totalCost - $this->totalDiscount;
        // $this->totalBenefit = $sales->flatMap(function ($sale) {
        //     return $sale->saleItems->map(function ($item) {
        //         $cost = $item->product->cost ?? 0;
        //         $price = $item->unit_price ?? 0;
        //         $qty = $item->quantity ?? 0;
        //         $discount = $item->discount ?? 0;
        //         return (($price - $cost) * $qty) - $item->sale->discount;
        //     });

        // })->sum();



        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::query())
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid')
                    ->label('Received')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d-M-Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('print')
                    ->label('Printed')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('until')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
