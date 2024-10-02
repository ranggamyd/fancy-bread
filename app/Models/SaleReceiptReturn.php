<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReceiptReturn extends Model
{
    use HasFactory;

    public function saleReceipt()
    {
        return $this->belongsTo(SaleReceipt::class);
    }

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }
}
