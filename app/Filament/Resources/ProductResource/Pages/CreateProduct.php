<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lastId = \App\Models\Product::max('id') + 1;
        $data['sku'] = 'SKU-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
