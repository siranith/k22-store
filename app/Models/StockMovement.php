<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    //
        protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'note',
    ];

    /**
     * Relationship: each stock movement belongs to one product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
