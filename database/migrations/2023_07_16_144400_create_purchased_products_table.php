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
            $table->foreignId('id')->unique()->constrained()->references('id')->on('products');
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
