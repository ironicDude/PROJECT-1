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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->nullable()->constrained('products', 'id')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('rating');
            $table->text('review')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
