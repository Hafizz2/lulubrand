<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_times', function (Blueprint $table) {
            $table->id();
            $table->string('time_label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pickup_time_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_time_id')->nullable()->constrained('pickup_times')->cascadeOnDelete();
            $table->date('override_date');
            $table->enum('status', ['full', 'blocked'])->default('full');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_time_overrides');
        Schema::dropIfExists('pickup_times');
    }
};
