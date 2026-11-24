<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('building_number', 50)->nullable()->after('address_line_2');
            $table->string('floor', 50)->nullable()->after('building_number');
            $table->string('apartment', 50)->nullable()->after('floor');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['building_number', 'floor', 'apartment']);
        });
    }
};
