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
        Schema::create('sale_receipt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_receipt_id');
            $table->string('method')->nullable();
            $table->string('provider')->nullable();
            $table->string('reference')->nullable();
            $table->float('amount');
            $table->float('fee')->nullable();
            $table->float('total');
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
        Schema::dropIfExists('sale_receipt_payments');
    }
};
