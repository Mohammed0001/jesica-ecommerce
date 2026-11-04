<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->unsignedTinyInteger('buy_quantity')->default(1)->after('value');
            $table->unsignedTinyInteger('get_quantity')->default(1)->after('buy_quantity');
        });
    }

    public function down()
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['buy_quantity', 'get_quantity']);
        });
    }
};
