<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Product;
use App\Models\StockMovement;

class Sale extends Model
{
    //
    protected $fillable = [
    'invoice_number',
    'customer_id',
    'user_id',
    'contact_number',
    'address',
    'delivery_fee',
    'note',
    'date',
    'total',
    'status',
    'paid',
    'payment_method',
    'cod',
    'customer_type',
    'discount',
    'contact_name',
    ];
    public function saleItems()
{
    return $this->hasMany(SaleItem::class);
}
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function calculateFinancials(array $cart, bool $deliveryFee = false, float $discount = 0): array
    {
        $subtotal = collect($cart)->sum('line_total');
        $fee = $deliveryFee ? 2.00 : 0.00;
        $total = round($subtotal + $fee, 2);
        $paid = max(0.00, round($total - $discount, 2));

        return [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => $fee,
            'total' => $total,
            'paid' => $paid,
            'discount' => round($discount, 2),
        ];
    }

    public static function createFromCart(array $data, array $cart, ?int $userId = null): self
    {
        $financials = self::calculateFinancials($cart, !empty($data['delivery_fee']), floatval($data['discount'] ?? 0));

        $sale = self::create([
            'invoice_number' => 'INV-' . now()->timestamp,
            'user_id' => $userId,
            'customer_type' => $data['customer_type'] ?? 'regular',
            'customer_id' => $data['customer_id'] ?? null,
            'contact_number' => $data['contact_number'] ?? '',
            'address' => $data['address'] ?? '',
            'delivery_fee' => $financials['delivery_fee'],
            'total' => $financials['total'],
            'discount' => $financials['discount'],
            'paid' => $financials['paid'],
            'cod' => $data['cod'] ?? false,
            'note' => ($data['cod'] ?? false) ? 'pending' : null,
            'contact_name' => $data['contact_name'] ?? '',
        ]);

        $sale->syncItemsAndStocks($cart);

        return $sale;
    }

    public function updateFromCart(array $data, array $cart): self
    {
        $financials = self::calculateFinancials($cart, !empty($data['delivery_fee']), floatval($data['discount'] ?? 0));

        $this->restoreStockFromSaleItems();
        $this->saleItems()->delete();

        $this->update([
            'customer_type' => $data['customer_type'] ?? 'regular',
            'customer_id' => $data['customer_id'] ?? null,
            'contact_number' => $data['contact_number'] ?? '',
            'address' => $data['address'] ?? '',
            'delivery_fee' => $financials['delivery_fee'],
            'total' => $financials['total'],
            'discount' => $financials['discount'],
            'paid' => $financials['paid'],
            'cod' => $data['cod'] ?? false,
            'contact_name' => $data['contact_name'] ?? '',
        ]);

        $this->syncItemsAndStocks($cart);

        return $this;
    }

    public function restoreStockFromSaleItems(): void
    {
        foreach ($this->saleItems as $oldItem) {
            $product = $oldItem->product;
            if (! $product) {
                continue;
            }

            $product->increment('stock', $oldItem->quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'quantity' => $oldItem->quantity,
                'type' => 'in',
                'reference_id' => $this->id,
                'note' => 'Edit sale - restore old stock (' . $this->invoice_number . ')',
            ]);
        }
    }

    public function syncItemsAndStocks(array $cart): void
    {
        foreach ($cart as $item) {
            $this->saleItems()->create([
                'product_id' => $item['product_id'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
            ]);

            $product = Product::find($item['product_id']);
            if (! $product) {
                continue;
            }

            StockMovement::create([
                'product_id' => $product->id,
                'quantity' => -abs($item['quantity']),
                'type' => 'out',
                'reference_id' => $this->id,
                'note' => 'sale #' . $this->invoice_number,
            ]);

            $product->decrement('stock', $item['quantity']);
        }
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->saleItems->sum('line_total');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->paid - (float) $this->total);
    }

    protected static function booted()
    {
        static::deleting(function ($sale) {
            // 🧠 When deleting a sale, restore each product’s stock
            foreach ($sale->saleItems as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);

                    // Optional: record this as a stock movement
                    \App\Models\StockMovement::create([
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'note' => 'Sale deleted - stock restored (Invoice: ' . $sale->invoice_number . ')',
                    ]);
                }
            }

            // 🧹 Optionally delete the related sale items too
            $sale->saleItems()->delete();
        });
    }
}
