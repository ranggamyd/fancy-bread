<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnInvoice extends Model
{
    use HasFactory;

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
