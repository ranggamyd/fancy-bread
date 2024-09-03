<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
