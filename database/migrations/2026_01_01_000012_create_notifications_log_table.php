<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->string('recipient');
            $table->enum('channel', ['telegram', 'sms', 'email']);
            $table->string('event_type');
            $table->text('message_body');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->text('error_details')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
    }
};
