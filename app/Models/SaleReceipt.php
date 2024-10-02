<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReceipt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = ['payment_status' => PaymentStatus::class];
    
    public function saleReceiptInvoices()
    {
        return $this->hasMany(SaleReceiptInvoice::class);
    }

    public function saleReceiptReturns()
    {
        return $this->hasMany(SaleReceiptReturn::class);
    }

    public function saleReceiptPayments()
    {
        return $this->hasMany(SaleReceiptPayment::class);
    }
}
