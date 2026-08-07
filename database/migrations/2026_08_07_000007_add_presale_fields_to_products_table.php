<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_presale')->default(false)->after('is_new')->index();
            $table->timestamp('presale_available_at')->nullable()->after('is_presale');
            $table->text('presale_note')->nullable()->after('presale_available_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_presale', 'presale_available_at', 'presale_note']);
        });
    }
};
