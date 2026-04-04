<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            // Per-area delivery fee (null = use global site_setting fallback)
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('code');
            // JSON list of city names that belong to this area (case-insensitive matching)
            // e.g. ["Cairo", "Al Qahirah", "القاهرة"]
            $table->json('city_names')->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'city_names']);
        });
    }
};
