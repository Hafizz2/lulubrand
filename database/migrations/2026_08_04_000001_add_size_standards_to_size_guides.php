<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds US/UK/EU size columns to size_guides table for multi-standard size chart.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('size_guides', function (Blueprint $table) {
            $table->string('us_size')->nullable()->after('name')->comment('US size equivalent (e.g. 4, 6, 8, 10)');
            $table->string('uk_size')->nullable()->after('us_size')->comment('UK size equivalent (e.g. 8, 10, 12, 14)');
            $table->string('eu_size')->nullable()->after('uk_size')->comment('EU size equivalent (e.g. 36, 38, 40, 42)');
        });
    }

    public function down(): void
    {
        Schema::table('size_guides', function (Blueprint $table) {
            $table->dropColumn(['us_size', 'uk_size', 'eu_size']);
        });
    }
};
