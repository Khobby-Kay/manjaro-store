<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alibaba_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('local_order_id');
            $table->string('alibaba_order_id')->nullable();
            $table->foreignId('supplier_id')->constrained('alibaba_suppliers');
            $table->foreignId('product_id')->constrained('alibaba_products');
            $table->integer('quantity');
            $table->decimal('total_cost', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->enum('status', ['pending', 'ordered', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->json('order_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alibaba_orders');
    }
};
