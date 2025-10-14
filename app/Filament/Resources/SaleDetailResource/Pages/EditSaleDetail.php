<?php

namespace App\Filament\Resources\SaleDetailResource\Pages;

use App\Filament\Resources\SaleDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaleDetail extends EditRecord
{
    protected static string $resource = SaleDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
