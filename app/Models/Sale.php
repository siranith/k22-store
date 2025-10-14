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
    ];
    public function saleItems()
{
    return $this->hasMany(SaleItem::class);
}
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
