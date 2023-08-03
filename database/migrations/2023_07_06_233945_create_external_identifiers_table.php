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
        Schema::create('external_identifiers', function (Blueprint $table) {
            $table->id();
            $table->string('drugbank_id', 12)->unique();
            $table->string('is_primary');
            $table->string('resource', 9);
            $table->string('url', 500);
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_identifiers');
    }
};
