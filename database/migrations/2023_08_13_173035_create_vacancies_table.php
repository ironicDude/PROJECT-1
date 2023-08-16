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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('title');
            $table->string('description');
            $table->string('type');
            $table->integer('salary');
            $table->date('posting_date');
            $table->date('deadline');
            $table->integer('number_of_vacancies');
            $table->string('status')->default('Alowed');
            $table->foreign('employee_id')
            ->references('id')
            ->on('employee_role')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
