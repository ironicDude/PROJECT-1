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
        Schema::create('purchased_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->references('id')->on('products');
            $table->foreignId('purchase_id')->constrained()->references('id')->on('purchases');
            $table->timestamps();
            $table->decimal('price', 10, 2);
            $table->integer('order_limit');
            $table->integer('minimum_stock_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_products');
    }
};
