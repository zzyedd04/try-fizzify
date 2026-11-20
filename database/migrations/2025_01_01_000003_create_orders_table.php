<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // dipakai sebagai order_id di Midtrans
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('total');

            $table->string('customer_name');
            $table->string('phone', 30);
            $table->string('address');

            // pending | paid | failed | expired | refunded
            $table->string('payment_status')->default('pending')->index();
            // new | confirmed | cancelled (diatur admin)
            $table->string('fulfillment_status')->default('new');
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            // penanda supaya "terjual" hanya bertambah satu kali per pesanan
            $table->boolean('counted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
