<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    public function saleReturnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
