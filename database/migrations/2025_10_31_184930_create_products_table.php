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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('story')->nullable(); // Brand story for product
            $table->decimal('price', 10, 2);
            $table->string('sku')->unique()->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('is_one_of_a_kind')->default(false);
            $table->boolean('visible')->default(true);
            $table->unsignedBigInteger('size_chart_id')->nullable(); // Remove constraint for now
            $table->timestamps();

            $table->index(['visible', 'collection_id']);
            $table->index('is_one_of_a_kind');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
