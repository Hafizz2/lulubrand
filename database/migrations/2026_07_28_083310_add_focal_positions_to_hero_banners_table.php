<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hero_banners', function (Blueprint $table) {
            if (!Schema::hasColumn('hero_banners', 'desktop_focal_position')) {
                $table->string('desktop_focal_position')->default('center center')->after('image_url');
            }
            if (!Schema::hasColumn('hero_banners', 'mobile_focal_position')) {
                $table->string('mobile_focal_position')->default('center center')->after('desktop_focal_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_banners', function (Blueprint $table) {
            $table->dropColumn(['desktop_focal_position', 'mobile_focal_position']);
        });
    }
};
