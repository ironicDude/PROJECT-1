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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 98);
            $table->string('description', 5046);
            $table->string('state', 6);
            $table->string('indication', 6105);
            $table->text('pharmacodynamics');
            $table->text('toxicity');
            $table->string('half_life', 1216);
            $table->string('route_of_elimination', 1409);
            $table->string('clearance', 1909);
            $table->string('attribute', 14);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
