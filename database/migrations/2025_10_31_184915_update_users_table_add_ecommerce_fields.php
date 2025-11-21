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
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('email');
            $table->string('phone')->nullable()->after('date_of_birth');
            $table->foreignId('region_id')->nullable()->constrained()->after('phone');
            $table->foreignId('role_id')->default(2)->constrained()->after('region_id'); // Default to CLIENT
            $table->string('profile_photo_path')->nullable()->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'date_of_birth',
                'phone',
                'region_id',
                'role_id',
                'profile_photo_path'
            ]);
        });
    }
};
