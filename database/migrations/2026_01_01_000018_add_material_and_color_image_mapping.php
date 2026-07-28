<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'material')) {
                $table->string('material')->nullable()->after('description');
            }
        });

        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'color_value')) {
                $table->string('color_value')->nullable()->after('url')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'material')) {
                $table->dropColumn('material');
            }
        });

        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'color_value')) {
                $table->dropColumn('color_value');
            }
        });
    }
};
