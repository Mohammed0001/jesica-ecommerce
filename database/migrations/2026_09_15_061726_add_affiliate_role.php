<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Deploys run `migrate --force` but not `db:seed`, so the AFFILIATE role
     * needs to exist as data change here rather than only in RoleSeeder.
     */
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            'name' => 'AFFILIATE',
            'description' => 'Affiliate who can track promo codes and referred orders',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'AFFILIATE')->delete();
    }
};
