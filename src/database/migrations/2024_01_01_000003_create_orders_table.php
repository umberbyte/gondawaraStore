<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_number');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('postal_code');
            $table->string('address');
            $table->string('phone');
            $table->integer('subtotal');
            $table->integer('tax');
            $table->integer('discount')->default(0);
            $table->integer('total_price'); // マイナス値も許容
            $table->string('payment_method')->default('cod'); // cod: 代金引換, bank: 銀行振込
            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable(); // 手書き熨斗要望など
            $table->string('status')->default('受注受付');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
