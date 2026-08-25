<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RepeatCustomersReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'filament.pages.repeat-customers-report';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'REPORT';
    protected static ?string $navigationLabel = 'Repeat Customers';
    protected static ?string $slug = 'repeat-customers-report';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 4;

    public int $totalRepeatCustomers = 0;
    public float $totalRevenue = 0;

    protected function getViewData(): array
    {
        $query = $this->getTableQuery();

        $results = $query->get();

        $this->totalRepeatCustomers = $results->count();
        $this->totalRevenue = $results->sum('total_spent');

        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('contact_number')
                    ->label('Contact Number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contact Name')
                    ->sortable()
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('purchases_count')
                    ->label('# Purchases')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_purchase')
                    ->label('Last Purchase')
                    ->dateTime('d-M-Y')
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('purchases_count', 'desc');
    }
    //  get a unique string identifier for each row. by contact_number
    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->contact_number;
    }

    protected function getTableQuery(): Builder
    {
        return Sale::query()
            ->select([
                'contact_number',
                DB::raw('MAX(contact_name) as contact_name'),
                DB::raw('COUNT(id) as purchases_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('MAX(created_at) as last_purchase'),
            ])
            ->whereNotNull('contact_number')
            ->where('contact_number', '!=', '')
            ->groupBy('contact_number')
            ->havingRaw('COUNT(id) >= 2');
    }
}
