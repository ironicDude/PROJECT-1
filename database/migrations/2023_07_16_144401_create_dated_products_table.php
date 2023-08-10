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
        Schema::create('dated_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('product_id')->constrained('purchased_products', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases', 'id')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->decimal('discount', 3, 2)->nullable();
            $table->integer('quantity', false, true);
            $table->date('expiry_date');
            $table->date('manufacturing_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dated_products');
    }
};
