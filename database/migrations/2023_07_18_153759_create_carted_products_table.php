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
        Schema::create('carted_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('customer_id')->on('customer_carts');
            $table->foreignId('purchased_product_id')->references('id')->on('purchased_products');
            $table->decimal('subtotal', 10, 2);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carted_products');
    }
};
