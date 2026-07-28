<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('logistics_mode')->default('pickup')->after('payment_status');
            $table->unsignedBigInteger('delivery_fee')->default(0)->after('logistics_mode');
            $table->date('preferred_date')->nullable()->after('delivery_fee');
            $table->string('preferred_time')->nullable()->after('preferred_date');
            $table->string('google_maps_link')->nullable()->after('preferred_time');
            $table->string('payment_proof')->nullable()->after('google_maps_link');
            $table->string('confirmed_transaction_id')->nullable()->after('payment_proof');
            $table->foreignId('bank_account_id')->nullable()->after('confirmed_transaction_id')->constrained('bank_accounts')->nullOnDelete();
            $table->unsignedBigInteger('deposit_amount')->default(0)->after('bank_account_id');
            $table->unsignedBigInteger('balance_due')->default(0)->after('deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn([
                'logistics_mode',
                'delivery_fee',
                'preferred_date',
                'preferred_time',
                'google_maps_link',
                'payment_proof',
                'confirmed_transaction_id',
                'bank_account_id',
                'deposit_amount',
                'balance_due',
            ]);
        });
    }
};
