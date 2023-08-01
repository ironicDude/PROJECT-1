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
        Schema::create('carted_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('cart_id')->conastrained('carts', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('prescription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carted_prescriptions');
    }
};
