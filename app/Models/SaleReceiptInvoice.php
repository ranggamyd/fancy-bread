<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReceiptInvoice extends Model
{
    use HasFactory;

    public function saleReceipt()
    {
        return $this->belongsTo(SaleReceipt::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
