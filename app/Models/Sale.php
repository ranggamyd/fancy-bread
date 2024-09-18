<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => Status::class,
        'payment_status' => PaymentStatus::class
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function saleReturnInvoices()
    {
        return $this->hasMany(SaleReturnInvoice::class);
    }
}
