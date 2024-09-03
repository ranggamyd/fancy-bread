<?php

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin/sales/{sale}/print', function (Sale $sale) {
    
    $data = [
        [
            'quantity' => 1,
            'description' => '1 Year Subscription',
            'price' => '129.00'
        ]
    ];
 
    $pdf = Pdf::loadView('saleInvoice', compact('sale'));
    return $pdf->stream();
})->name('sales.invoice.print');
