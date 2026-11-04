<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->enum('type', ['percentage', 'fixed', 'bogo'])->default('percentage')->change();
        });
    }

    public function down()
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->enum('type', ['percentage', 'fixed'])->default('percentage')->change();
        });
    }
};
