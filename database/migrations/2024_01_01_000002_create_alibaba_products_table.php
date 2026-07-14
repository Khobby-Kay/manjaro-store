<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alibaba_products', function (Blueprint $table) {
            $table->id();
            $table->string('alibaba_product_id')->unique();
            $table->foreignId('supplier_id')->constrained('alibaba_suppliers')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('original_price', 10, 2);
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('retail_price', 10, 2);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('stock_quantity')->default(0);
            $table->json('images')->nullable();
            $table->json('specifications')->nullable();
            $table->json('shipping_info')->nullable();
            $table->enum('status', ['pending', 'imported', 'error'])->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->unsignedBigInteger('local_product_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alibaba_products');
    }
};
