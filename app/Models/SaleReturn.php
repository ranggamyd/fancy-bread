<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturn extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function saleReturnInvoices()
    {
        return $this->hasMany(SaleReturnInvoice::class);
    }

    public function saleReturnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function saleReceiptReturn()
    {
        return $this->hasOne(SaleReceiptReturn::class);
    }
}
