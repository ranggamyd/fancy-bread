<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = ['status' => Status::class];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function SaleReturnInvoice()
    {
        return $this->hasOne(SaleReturnInvoice::class);
    }

    public function SaleReceiptInvoice()
    {
        return $this->hasOne(SaleReceiptInvoice::class);
    }

    public function Driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
