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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('service')->index(); // e.g., 'BOSTA', 'PayPal', etc.
            $table->string('method'); // HTTP method: GET, POST, PUT, DELETE
            $table->string('endpoint'); // API endpoint URL
            $table->text('request_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->integer('response_status')->nullable(); // HTTP status code
            $table->text('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->decimal('duration', 8, 2)->nullable(); // Request duration in seconds
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->morphs('loggable'); // Polymorphic relation (e.g., Order, Shipment)
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['service', 'created_at']);
            $table->index('response_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
