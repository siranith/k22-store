<?php

namespace App\Filament\Resources\SaleDetailResource\Pages;

use App\Filament\Resources\SaleDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSaleDetail extends ViewRecord
{
    protected static string $resource = SaleDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
