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
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;

class CreateSaleTransaction extends Page implements Forms\Contracts\HasForms, Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Create Sale Transaction';
    protected static ?string $navigationGroup = 'POS';
    protected static string $view = 'filament.pages.create-sale-transaction';

    public ?array $data = [];
    public $cart = []; // store added products

    public $selected_category = null;
    public $selected_product = null;
    public $unit_price = 0;
    public $quantity = 1;
    public $search_product_text = '';
    public ?int $sale_id = null;

public function mount(?int $sale_id = null)
{
    // Support passing sale_id either via mount param or query string (e.g. /admin/create-sale-transaction?sale_id=1)
    if (is_null($sale_id)) {
        $sale_id = request()->query('sale_id');
    }

    $this->sale_id = $sale_id;

    if ($sale_id) {
        // $sale = Sale::with('items.product')->find($sale_id);
        $sale = Sale::with('saleItems.product')->find($sale_id);

        if ($sale) {
            $this->form->fill([
                'customer_type' => $sale->customer_type,
                'customer_id' => $sale->customer_id,
                'contact_number' => $sale->contact_number,
                'address' => $sale->address,
                'delivery_fee' => $sale->delivery_fee,
                'discount' => $sale->discount,
            ]);

            // $this->cart = $sale->items->map(fn ($item) => [
            //     'product_id' => $item->product_id,
            //     'product_name' => $item->product->name,
            //     'unit_price' => $item->unit_price,
            //     'quantity' => $item->quantity,
            //     'line_total' => $item->unit_price * $item->quantity,
            // ])->toArray();
            $this->cart = $sale->saleItems->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->unit_price * $item->quantity,
            ])->toArray();

        } else {
            // Sale id provided but not found — notify and redirect back to sales list
            Notification::make()
                ->title('Sale not found')
                ->danger()
                ->body('The sale you are trying to edit does not exist or has been deleted.')
                ->send();

            $this->redirect(SaleResource::getUrl('index'));
            return;
        }
    }
}


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_type')
                    ->label('Customer Type')
                    ->options([
                        'regular' => 'Regular',
                        'member' => 'Member',
                        'walkin' => 'Walk-in',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('customer_id', null)),

                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->options(fn () => Customer::pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn (callable $get) => $get('customer_type') === 'member'),

                Forms\Components\TextInput::make('contact_number')
                    ->label('Contact Number')
                    ->visible(fn (callable $get) => $get('customer_type') === 'regular')
                    ->required(fn (callable $get) => $get('customer_type') === 'regular')
                    ->numeric()
                    ->minLength(8)
                    ->maxLength(15),

                Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->visible(fn (callable $get) => $get('customer_type') === 'regular')
                    ->required(fn (callable $get) => $get('customer_type') === 'regular'),
                Forms\Components\Checkbox::make('delivery_fee')
                    ->label('Delivery fee ($1.50)')
                    ->default(false)
                    ->reactive(),
                Forms\Components\TextInput::make('discount')
                    ->label('Discount ($)')
                    ->numeric()
                    ->default(0),
            ])
            ->statePath('data');
    }
public function table(Table $table): Table
{
    return $table
        ->query(Product::query()->where('is_active', true))
        ->columns([
            ImageColumn::make('image')
    ->disk('public')
    ->height(60)
    ->url(fn ($record) => Storage::disk('public')->url($record->image))
    ->openUrlInNewTab()
    ->defaultImageUrl(asset('images/no-image.png')),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->label('Category')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->label('Price')
                ->money('usd', true)
                ->sortable(),
            Tables\Columns\BadgeColumn::make('stock')
                ->sortable()
                ->colors([
                    'danger' => fn ($state) => $state < 5,
                    'success' => fn ($state) => $state >= 5,
                ]),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category')
                ->relationship('category', 'name')
                ->label('Category'),
        ])
        ->actions([
            Tables\Actions\Action::make('add_to_cart')
                ->label('Add to Cart')
                ->button()
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('quantity')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (Product $record, array $data) {
                    $this->addProductFromTable($record, $data['quantity']);
                }),
        ]);
}

public function addProductFromTable(Product $product, int $quantity = 1)
{
    $existingIndex = collect($this->cart)->search(fn ($item) => $item['product_id'] === $product->id);

    if ($existingIndex !== false) {
        // Update quantity if product already in cart
        $this->cart[$existingIndex]['quantity'] += $quantity;
        $this->cart[$existingIndex]['line_total'] = $this->cart[$existingIndex]['quantity'] * $product->price;
    } else {
        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ];
    }

    \Filament\Notifications\Notification::make()
        ->title("Added {$quantity} × {$product->name} to cart!")
        ->success()
        ->send();
}

public function removeProduct($index)
{
    unset($this->cart[$index]);
    $this->cart = array_values($this->cart); // reindex after removing

    \Filament\Notifications\Notification::make()
        ->title('Product removed from cart')
        ->danger()
        ->send();
}

public function submit()
{
    $data = $this->form->getState();

    if (empty($this->cart)) {
        Filament\Notifications\Notification::make()
            ->title('Cart is empty!')
            ->danger()
            ->send();
        return;
    }

    DB::transaction(function () use ($data) {
        $subtotal = collect($this->cart)->sum('line_total');
        $fee = (!empty($data['delivery_fee'])) ? 1.5 : 0;
        $totalAmount = $subtotal + $fee;
        $paid = $totalAmount - ($data['discount'] ?? 0);

        if ($this->sale_id) {
            $sale = Sale::find($this->sale_id);
            $sale->update([
                'customer_type'  => $data['customer_type'] ?? 'regular',
        'customer_id'    => $data['customer_id'] ?? null,
        'contact_number' => $data['contact_number'] ?? '',
        'address'        => $data['address'] ?? '',
        'total'          => $totalAmount,
        'discount'       => $data['discount'] ?? 0,          // <-- Add this
        'paid'           => $totalAmount - ($data['discount'] ?? 0), // <-- Correct paid

            ]);

    // 🔁 Restore stock before deleting old items
           foreach ($sale->saleItems as $oldItem) {
        $product = $oldItem->product;
        if ($product) {
            $product->increment('stock', $oldItem->quantity);

            \App\Models\StockMovement::create([
                'product_id' => $product->id,
                'quantity' => $oldItem->quantity,
                'type' => 'in',
                'reference_id' => $sale->id,
                'note' => 'Edit sale - restore old stock (' . $sale->invoice_number . ')',
            ]);
        }
    }

    // 🧹 Now safe to delete old items
    $sale->saleItems()->delete();
        } else {
                    // ✅ Create new sale
                    $sale = Sale::create([
                        'invoice_number' => 'INV-' . now()->timestamp,
                        'user_id'        => auth()->id(),
                        'customer_type'  => $data['customer_type'] ?? 'regular',
                        'customer_id'    => $data['customer_id'] ?? null,
                        'contact_number' => $data['contact_number'] ?? '',
                        'address'        => $data['address'] ?? '',
                        'total'          => $totalAmount,
                        'discount'      => $data['discount'] ?? 0,
                        'paid'           => $totalAmount - ($data['discount'] ?? 0),
                    ]);
                }

                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'line_total' => $item['line_total'],
                    ]);
                    // Update stock movement (record outgoing stock)
                        StockMovement::create([
                            'product_id' => $item['product_id'],
                            'quantity' => -$item['quantity'], // negative means stock out
                            'type' => 'out',
                            'reference_id' => $sale->id,
                            'note' => 'sale #' . $sale->invoice_number,
                        ]);

                        $product = Product::find($item['product_id']);
                        if ($product) {
                            $product->decrement('stock', $item['quantity']);
                        }
                }
            });
            \Filament\Notifications\Notification::make()
                ->title($this->sale_id ? 'Sale updated successfully!' : 'Sale created successfully!')
                ->success()
                ->send();

            $this->redirect(SaleResource::getUrl('index'));
}



}
