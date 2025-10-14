<?php
namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Filament\Resources\SaleResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Forms\Form\Components\Select;
use App\Models\StockMovement;

class CreateSaleTransaction extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Create Sale Transaction';
    protected static ?string $navigationGroup = 'POS'; // optional group
    protected static string $view = 'filament.pages.create-sale-transaction';

    // this is required for form state
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
        ->schema([
        Forms\Components\Select::make('customer_type')
            ->label('Customer Type')
            ->options([
                'regular' => 'Regular',
                'member' => 'Member',
            ])
            ->required()
            ->reactive()
            ->afterStateUpdated(fn ($state, callable $set) => $set('customer_id', null)),
        Forms\Components\Select::make('customer_id')
            ->label('Customer')
            ->options(fn () => Customer::pluck('name', 'id'))
            ->searchable()
            ->reactive()
            ->visible(fn (callable $get) => $get('customer_type') === 'member'),
        Forms\Components\TextInput::make('contact_number')
            ->label('Contact Number')
            ->reactive()
            ->visible(fn (callable $get) => $get('customer_type') === 'regular'),
        Forms\Components\TextInput::make('address')
            ->label('Address')
            ->reactive()
            ->visible(fn (callable $get) => $get('customer_type') === 'regular'),
        Forms\Components\Repeater::make('sale_items')
            ->label('Products')
            ->schema([
                // 1. Category select
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(fn () => \App\Models\Category::pluck('name', 'id'))
                    ->reactive()
                    ->required(),

                // 2. Product select, filtered by category
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(function (callable $get) {
                        $categoryId = $get('category_id');
                        if (!$categoryId) {
                            return [];
                        }
                        return \App\Models\Product::where('category_id', $categoryId)->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) =>
                        $set('unit_price', \App\Models\Product::find($state)?->price ?? 0)
                    )
                    ->required(),

                Forms\Components\TextInput::make('unit_price')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                        $set('line_total', $state * $get('unit_price'))
                    )
                    ->required(),

                Forms\Components\TextInput::make('line_total')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
            ])
            ->columns(5)
            ->createItemButtonLabel('Add Product'),
        // ...existing code...
                    ])
                    ->statePath('data'); // 👈 very important
            }


        public function submit()
        {
            $data = $this->form->getState();

            if (empty($data['sale_items']) || !is_array($data['sale_items'])) {
                return;
            }

            DB::transaction(function () use ($data) {
                $sale = Sale::create([
                    'invoice_number'   => 'INV-' . now()->timestamp,
                    'user_id'          => auth()->id(), // ✅ Filament-authenticated user
                    'customer_type'    => $data['customer_type'] ?? 'regular', // ✅ save type
                    'customer_id'      => $data['customer_id'] ?? null,        // optional if exists
                    'contact_number'   => $data['contact_number'] ?? '',
                    'address'          => $data['address'] ?? '',
                    'total'            => collect($data['sale_items'])->sum('line_total'),
                    'paid'             => collect($data['sale_items'])->sum('line_total'),
                ]);


                foreach ($data['sale_items'] as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'line_total' => $item['line_total'],
                    ]);
                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'type'       => 'out',
                        'note'       => 'Stock decreased due to sale #' . $sale->invoice_number,
                    ]);

                    // Optionally update product stock
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }
            });
            $this->redirect(SaleResource::getUrl('index'));
        }

}
