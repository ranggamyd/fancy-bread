<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('branch');
            $table->text('notes')->nullable();
            $table->integer('invoice_items');
            $table->float('total_invoice');
            $table->integer('return_items');
            $table->float('total_return');
            $table->float('subtotal');
            $table->float('fee');
            $table->float('grandtotal');
            $table->enum('payment_status', ['unpaid', 'uncomplete', 'paid'])->default('unpaid');
            $table->dateTime('date')->defaultNow();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_receipts');
    }
};
