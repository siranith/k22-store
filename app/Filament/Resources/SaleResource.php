<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
protected static ?string $navigationGroup = 'POS';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_number')
                    ->label('Contact Number')
                    ->getStateUsing(function ($record) {
                        $contact = $record->contact_number;
                        $customerPhone = $record->customer?->phone;
                        return $contact && $customerPhone && $contact !== $customerPhone
                            ? "{$contact} ({$customerPhone})"
                            : ($contact ?: ($customerPhone ?? '—'));
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->getStateUsing(function ($record) {
                        $saleAddress = $record->address;
                        $customerAddress = $record->customer?->address;

                        return $saleAddress && $customerAddress && $saleAddress !== $customerAddress
                            ? "{$saleAddress} ({$customerAddress})"
                            : ($saleAddress ?: ($customerAddress ?? '—'));
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('usd', true)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'view' => Pages\ViewSale::route('/{record}'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

}
