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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable;
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('status')->default("active");
            $table->foreignId('user_role_id')->constrained();
            $table->string('role');
            $table->integer('role_id')->unsigned();
            $table->integer('mobile')->nullable();
            $table->string('gender',6)->nullable();
            $table->date('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
