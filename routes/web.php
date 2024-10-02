<?php

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\SaleReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;

Route::get('/', function () {
    // return view('welcome');
    return redirect('admin');
});

Route::get('admin/purchases/{purchase}/print', function (Purchase $purchase) {
    $pdf = Pdf::loadView('purchaseInvoice', compact('purchase'));
    return $pdf->stream();
})->name('purchases.invoice.print');

Route::get('admin/sales/{sale}/print', function (Sale $sale) {
    $pdf = Pdf::loadView('saleInvoice', compact('sale'));
    return $pdf->stream();
})->name('sales.invoice.print');

Route::get('admin/sale-receipts/receipt/{saleReceipt}/print', function (SaleReceipt $saleReceipt) {
    $pdf = Pdf::loadView('saleReceipt', compact('saleReceipt'));
    return $pdf->stream();
})->name('saleReceipts.receipt.print');

Route::get('admin/sale-receipts/invoices/{saleReceipt}/print', function (SaleReceipt $saleReceipt) {
    $pdf = Pdf::loadView('saleReceiptInvoices', compact('saleReceipt'));
    return $pdf->stream();
})->name('saleReceipts.invoices.print');