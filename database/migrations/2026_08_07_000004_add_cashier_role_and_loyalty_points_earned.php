<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points_earned')->default(0);
            $table->unsignedInteger('loyalty_points_redeemed')->default(0);
            $table->unsignedInteger('loyalty_discount_cents')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points_earned',
                'loyalty_points_redeemed',
                'loyalty_discount_cents'
            ]);
        });
    }
};
