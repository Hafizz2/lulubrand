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
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone')->index();
            $table->text('customer_address');
            $table->string('customer_city');
            $table->enum('status', ['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending')->index();
            $table->enum('payment_method', ['cod', 'manual_mobile_money', 'card'])->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->unsignedBigInteger('subtotal')->comment('Stored in minor units');
            $table->unsignedBigInteger('discount_amount')->default(0)->comment('Stored in minor units');
            $table->unsignedBigInteger('total')->comment('Stored in minor units');
            $table->string('telegram_chat_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_title'); // Snapshot at order time
            $table->string('variant_sku'); // Snapshot at order time
            $table->unsignedBigInteger('unit_price')->comment('Snapshot unit price in minor units');
            $table->integer('quantity');
            $table->unsignedBigInteger('total_price')->comment('Snapshot total price in minor units');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
