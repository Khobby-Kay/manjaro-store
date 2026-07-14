<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alibaba_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alibaba_id')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_products')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('api_credentials')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alibaba_suppliers');
    }
};
