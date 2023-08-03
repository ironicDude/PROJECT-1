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
        Schema::create('ordered_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_id')->constrained('orders', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('dated_product_id')->nullable()->constrained('dated_products', 'id')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('quantity');
            $table->decimal('subtotal', 20, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_products');
    }
};
