<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'supplier_type')) {
                $table->string('supplier_type')->default('manual')->after('address');
            }

            if (! Schema::hasColumn('suppliers', 'central_tenant_id')) {
                $table->unsignedBigInteger('central_tenant_id')->nullable()->after('supplier_type');
            }

            $table->index(['supplier_type', 'central_tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['supplier_type', 'central_tenant_id']);
            $table->dropColumn(['supplier_type', 'central_tenant_id']);
        });
    }
};
