<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url('/admin/create-sale-transaction'),
        ];
    }

    // Show total paid for the currently applied table query (respects filters)
    protected function getTableHeading(): ?string
    {
        try {
            $sum = $this->getTableQuery()->sum('paid');
        } catch (\Throwable $e) {
            $sum = 0;
        }

        return 'Sales — Total Paid: $' . number_format($sum, 2);
    }
}
