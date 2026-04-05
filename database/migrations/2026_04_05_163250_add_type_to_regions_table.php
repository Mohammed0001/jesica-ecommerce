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
        Schema::table('regions', function (Blueprint $table) {
            // 'cairo_district' = area within Cairo (e.g. Maadi, Nasr City)
            // 'governorate'    = Egyptian governorate other than Cairo (e.g. Alexandria, Giza)
            $table->enum('type', ['cairo_district', 'governorate'])
                  ->default('governorate')
                  ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
