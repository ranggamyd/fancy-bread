<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReceiptPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function saleReceipt()
    {
        return $this->belongsTo(SaleReceipt::class);
    }
}
