<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('country')->default('Ethiopia');
            $table->string('city'); // e.g. Addis Ababa, Hawassa, Adama
            $table->string('district')->nullable(); // e.g. Bole, Mexico, Megenagna (for Addis Ababa)
            $table->unsignedInteger('cost_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
