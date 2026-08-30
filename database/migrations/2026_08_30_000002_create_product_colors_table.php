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
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                       // "Ivory", "Deep Navy"
            $table->string('hex_code', 7)->nullable();    // "#f5f0e6" — drives the swatch
            $table->integer('order')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'name']);
            $table->index(['product_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
