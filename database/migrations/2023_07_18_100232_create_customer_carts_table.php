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
        Schema::create('customer_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->references('id')->on('users');
            $table->timestamps();
            $table->decimal('shipping_fee')->nullable();
            $table->string('address')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_carts');
    }
};
