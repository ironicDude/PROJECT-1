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
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('account_status')->default('Active');
            $table->string('type');
            $table->integer('mobile')->nullable();
            $table->string('gender')->default('I prefer not to say');
            $table->date('date_of_birth');
            $table->string('image')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('personal_email')->nullable();
            $table->date('date_of_joining')->nullable()->default(now());
            $table->decimal('money', 10, 2)->nullable();
            $table->boolean('logout')->default(false);
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
