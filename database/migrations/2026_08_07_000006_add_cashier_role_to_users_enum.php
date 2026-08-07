<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum column in MySQL to include 'cashier'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'staff', 'cashier', 'customer') NOT NULL DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum list
        // Note: Cashier roles will fail to convert back unless updated first
        DB::statement("UPDATE users SET role = 'staff' WHERE role = 'cashier'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'staff', 'customer') NOT NULL DEFAULT 'customer'");
    }
};
