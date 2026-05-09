<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropUnique('daily_closing_unique_day');
        });

        Schema::table('daily_closings', function (Blueprint $table) {
            $table->foreignId('cashier_id')
                ->nullable()
                ->change();
        });

        Schema::table('daily_closings', function (Blueprint $table) {
            $table->unique(
                ['pharmacy_id', 'branch_id', 'closing_date'],
                'daily_closing_unique_branch_day'
            );
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropUnique('daily_closing_unique_branch_day');
        });

        Schema::table('daily_closings', function (Blueprint $table) {
            $table->foreignId('cashier_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('daily_closings', function (Blueprint $table) {
            $table->unique(
                ['pharmacy_id', 'branch_id', 'cashier_id', 'closing_date'],
                'daily_closing_unique_day'
            );
        });
    }
};