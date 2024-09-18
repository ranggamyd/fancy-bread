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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('invoice')->unique();
            $table->string('goods_receipt_number')->unique()->nullable();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'delivered', 'returned'])->default('new');
            $table->integer('total_items');
            $table->float('subtotal');
            $table->float('shipping_price')->default(0);
            $table->float('total_discount')->default(0);
            $table->float('grandtotal');
            $table->enum('payment_status', ['unpaid', 'uncomplete', 'paid'])->default('unpaid');
            $table->dateTime('date')->defaultNow();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
