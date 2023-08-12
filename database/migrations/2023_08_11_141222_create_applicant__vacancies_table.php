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
        Schema::create('applicant__vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('vacancy_id');
            $table->date('dateTime')->default('2023-07-11');
            $table->string('status')->default('null');
            $table->foreign('applicant_id')
            ->references('id')
            ->on('applicants')
            ->onDelete('cascade');
            $table->foreign('vacancy_id')
            ->references('id')
            ->on('vacancies')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant__vacancies');
    }
};
