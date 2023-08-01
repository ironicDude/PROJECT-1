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
            $table->foreignId('cart_id')->conastrained('carts', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('dated_product_id')->nullable()->constrained('dated_products', 'id')->nullOnDelete()->cascadeOnUpdate();
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
