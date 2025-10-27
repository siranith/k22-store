<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use App\Models\StockMovement;
use Filament\Tables\Columns\ImageColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                forms\Components\TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->step('any')
                    ->minValue(0),
                forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(fn ($context) => $context === 'edit'),
                forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\FileUpload::make('image')
                    ->label('Product Image')
                    ->image()
                    ->disk('public') // store in storage/app/public
                    ->directory('products') // saved as products/filename.jpg
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(2048)
                    ->nullable()
                    ->columnSpanFull(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                tables\Columns\TextColumn::make('sku')->searchable()->sortable(),
                tables\Columns\TextColumn::make('category.name')->label('Category')->searchable()->sortable(),
                tables\Columns\TextColumn::make('price')->money('usd', true)->sortable(),
                tables\Columns\TextColumn::make('cost')->money('usd', true)->sortable(),
                tables\Columns\BadgeColumn::make('stock')
                ->sortable()
                ->colors([
                    'danger' => fn ($state) => $state < 5,
                ]),

               ImageColumn::make('image')
    ->disk('public')
    ->url(fn ($record) => Storage::disk('public')->url($record->image))
    ->defaultImageUrl(asset('images/no-image.png')),

                tables\Columns\BooleanColumn::make('is_active')->label('Active')->sortable(),
                tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->label('Category'),

            ])
            ->actions([
                Tables\Actions\Action::make('purchase')
                    ->label('Purchase')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('note')
                            ->label('Note')
                            ->nullable(),
                    ])
                    ->action(function (Product $record, array $data) {
                        // create stock movement and update product stock
                        StockMovement::create([
                            'product_id' => $record->id,
                            'quantity' => $data['quantity'],
                            'type' => 'in',
                            'note' => $data['note'] ?? null,
                        ]);

                        $record->increment('stock', $data['quantity']);

                        \Filament\Notifications\Notification::make()
                            ->title("Added {$data['quantity']} to {$record->name}")
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
