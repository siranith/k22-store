<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    protected $fillable = [
    'invoice_number',
    'customer_id',
    'user_id',
    'contact_number',
    'address',
    'date',
    'total',
    'status',
    'paid',
    'payment_method',
    'customer_type',
    'discount',
    ];
    public function saleItems()
{
    return $this->hasMany(SaleItem::class);
}
    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
