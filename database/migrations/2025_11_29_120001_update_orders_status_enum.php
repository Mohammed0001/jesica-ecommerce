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
        // Add missing status values (e.g. 'pending', 'processing', 'delivered')
        // to align the DB enum with values used by controllers/UI.
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft','pending_deposit','pending','processing','paid_deposit','paid_full','shipped','delivered','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original status enum values
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft','pending_deposit','paid_deposit','paid_full','shipped','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
