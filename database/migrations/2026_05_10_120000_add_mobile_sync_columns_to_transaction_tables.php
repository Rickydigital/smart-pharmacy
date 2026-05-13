<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $transactionTables = [
        'purchases',
        'expenses',
        'sales_returns',
        'stock_adjustments',
        'stock_transfers',
        'daily_closings',
    ];

    public function up(): void
    {
        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'mobile_reference')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('mobile_reference')->nullable()->unique();
                $table->string('device_name')->nullable();
                $table->string('app_version', 50)->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamp('offline_created_at')->nullable();
            });
        }

        foreach ($this->transactionTables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'mobile_reference')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('mobile_reference')->nullable()->index();
                $table->string('device_name')->nullable();
                $table->string('app_version', 50)->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamp('offline_created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (array_merge(['sales'], $this->transactionTables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'mobile_reference')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if ($tableName === 'sales') {
                    $table->dropUnique(['mobile_reference']);
                } else {
                    $table->dropIndex($tableName.'_mobile_reference_index');
                }

                $table->dropColumn([
                    'mobile_reference',
                    'device_name',
                    'app_version',
                    'synced_at',
                    'offline_created_at',
                ]);
            });
        }
    }
};
