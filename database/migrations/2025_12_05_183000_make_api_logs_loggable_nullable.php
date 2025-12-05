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
        Schema::table('api_logs', function (Blueprint $table) {
            // Make loggable_type and loggable_id nullable
            $table->string('loggable_type')->nullable()->change();
            $table->unsignedBigInteger('loggable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            // Revert to non-nullable
            $table->string('loggable_type')->nullable(false)->change();
            $table->unsignedBigInteger('loggable_id')->nullable(false)->change();
        });
    }
};
