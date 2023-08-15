<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('account_status', ['active', 'inactive']);
            $table->enum('type', ['admin', 'user']);
            $table->string('mobile');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->string('image')->nullable();
            $table->decimal('salary', 8, 2);
            $table->string('personal_email')->nullable();
            $table->date('date_of_joining');
            $table->decimal('money', 8, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}