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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('drug_id')->constrained('drugs', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('interacting_drug_id')->constrained('interacting_drugs', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
