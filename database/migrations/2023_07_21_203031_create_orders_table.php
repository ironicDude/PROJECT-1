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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('employee_id')->nullable()->references('id')->on('users');
            $table->foreignId('customer_id')->constrained()->references('id')->on('users');
            $table->string('status')->default('Review');
            $table->decimal('shipping_fees', 20, 2)->default(0);
            $table->string('shipping_address');
            $table->string('method')->default('Online');
            $table->date('delivery_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
