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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('provider')->default('bosta'); // For future multi-provider support
            $table->string('tracking_number')->unique()->nullable();
            $table->string('bosta_delivery_id')->unique()->nullable(); // BOSTA internal ID
            $table->string('awb_number')->nullable(); // Air Waybill number
            $table->string('status')->default('pending'); // pending, in_transit, delivered, cancelled, etc.
            $table->decimal('cod_amount', 10, 2)->default(0); // Cash on delivery amount
            $table->boolean('is_cod')->default(false);
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->json('bosta_response')->nullable(); // Store full BOSTA API response
            $table->json('tracking_history')->nullable(); // Store tracking updates
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('tracking_number');
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
