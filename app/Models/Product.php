<?php

namespace App\Models;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku','name','category_id','price','cost','stock','is_active','image'];

    protected static function booted()
    {
        static::created(function ($product) {
            // Only create a stock movement if quantity exists
            if (!empty($product->stock) && $product->stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity'   => $product->stock,
                    'type'       => 'in',
                    'note'       => 'Initial stock on product creation',
                ]);
            }
        });
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

}

