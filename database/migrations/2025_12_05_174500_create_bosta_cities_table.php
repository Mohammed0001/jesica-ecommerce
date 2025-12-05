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
        Schema::create('bosta_cities', function (Blueprint $table) {
            $table->id();
            $table->string('bosta_id')->unique(); // BOSTA's city ID (e.g., FceDyHXwpSYYF9zGW)
            $table->string('name'); // English name (e.g., Cairo)
            $table->string('name_ar'); // Arabic name
            $table->string('code')->nullable(); // City code (e.g., EG-01)
            $table->string('alias')->nullable(); // Alternative spelling
            $table->integer('sector')->nullable(); // BOSTA sector number
            $table->boolean('pickup_availability')->default(true);
            $table->boolean('drop_off_availability')->default(true);
            $table->json('hub')->nullable(); // Hub information
            $table->timestamps();

            $table->index('name');
            $table->index('bosta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bosta_cities');
    }
};
