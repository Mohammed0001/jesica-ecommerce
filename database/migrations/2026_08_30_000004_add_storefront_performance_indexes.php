<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for the queries the storefront runs on every page: the newest /
     * cheapest / most expensive visible products, and the "is anything in
     * stock?" lookup that drives the Sold Out badge.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['visible', 'created_at'], 'products_visible_created_index');
            $table->index(['visible', 'price'], 'products_visible_price_index');
            $table->index(['visible', 'is_sold_out'], 'products_visible_sold_out_index');
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->index(['product_id', 'quantity'], 'product_sizes_product_quantity_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_visible_created_index');
            $table->dropIndex('products_visible_price_index');
            $table->dropIndex('products_visible_sold_out_index');
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropIndex('product_sizes_product_quantity_index');
        });
    }
};
