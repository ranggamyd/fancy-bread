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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->float('pre_tax_price')->default(0);
            $table->float('post_tax_price')->default(0);
            $table->float('cost')->default(0);
            $table->float('margin')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->integer('stock')->default(1);
            $table->integer('security_stock')->default(1);
            $table->enum('unit_type', ['Pcs', 'Pack/Box', 'Kg'])->default('Pcs');
            $table->integer('total_items')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
