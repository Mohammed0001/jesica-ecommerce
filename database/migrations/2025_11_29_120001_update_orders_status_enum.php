<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUSES = [
        'draft',
        'pending_deposit',
        'pending',
        'processing',
        'paid_deposit',
        'paid_full',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
    ];

    private const ORIGINAL_STATUSES = [
        'draft',
        'pending_deposit',
        'paid_deposit',
        'paid_full',
        'shipped',
        'completed',
        'cancelled',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing status values (e.g. 'pending', 'processing', 'delivered')
        // to align the DB enum with values used by controllers/UI.
        $this->setStatuses(self::STATUSES);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original status enum values
        $this->setStatuses(self::ORIGINAL_STATUSES);
    }

    /**
     * Rewrite the status column to accept exactly the given values.
     *
     * MODIFY ... ENUM is MySQL-only syntax, so on any other driver (SQLite in
     * the test suite, Postgres) the column becomes a plain string. The allowed
     * values are enforced in the application either way.
     *
     * @param  array<int, string>  $statuses
     */
    private function setStatuses(array $statuses): void
    {
        if (DB::getDriverName() === 'mysql') {
            $values = collect($statuses)->map(fn (string $s) => "'{$s}'")->implode(',');

            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM({$values}) NOT NULL DEFAULT 'draft'");

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }
};
