<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('returned_amount', 15, 2)->default(0)->after('balance_amount');
            $table->unsignedInteger('returned_base_units')->default(0)->after('returned_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'returned_amount',
                'returned_base_units',
            ]);
        });
    }
};