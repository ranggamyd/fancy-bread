<?php

use App\Models\Sale;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin/purchases/{purchase}/print', function (Purchase $purchase) {
    $pdf = Pdf::loadView('purchaseInvoice', compact('purchase'));
    return $pdf->stream();
})->name('purchases.invoice.print');

Route::get('admin/sales/{sale}/print', function (Sale $sale) {
    $pdf = Pdf::loadView('saleInvoice', compact('sale'));
    return $pdf->stream();
})->name('sales.invoice.print');
