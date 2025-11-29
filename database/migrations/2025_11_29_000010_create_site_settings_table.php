<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('site_settings')->insert([
            ['key' => 'delivery_fee', 'value' => '15'],
            ['key' => 'delivery_threshold', 'value' => '200'],
            ['key' => 'tax_percentage', 'value' => '14'],
            ['key' => 'service_fee_percentage', 'value' => '0'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
