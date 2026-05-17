<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_intelligence_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('period_type')->default('monthly'); // daily, weekly, monthly
            $table->date('period_start');
            $table->date('period_end');

            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('sales_base_units')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('profit_margin', 8, 2)->default(0);

            $table->unsignedInteger('public_searches')->default(0);
            $table->unsignedInteger('missed_searches')->default(0);

            $table->unsignedInteger('current_stock_base_units')->default(0);
            $table->decimal('avg_daily_sales_base_units', 12, 2)->default(0);
            $table->decimal('stock_cover_days', 12, 2)->nullable();

            $table->unsignedInteger('near_expiry_base_units')->default(0);
            $table->unsignedInteger('expired_base_units')->default(0);

            $table->unsignedTinyInteger('sales_score')->default(0);
            $table->unsignedTinyInteger('search_score')->default(0);
            $table->unsignedTinyInteger('profit_score')->default(0);
            $table->unsignedTinyInteger('stock_risk_score')->default(0);
            $table->unsignedTinyInteger('expiry_risk_score')->default(0);
            $table->unsignedTinyInteger('priority_score')->default(0);

            $table->string('recommendation_type')->nullable();
            $table->text('recommendation_text')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique([
                'pharmacy_id',
                'branch_id',
                'product_id',
                'period_type',
                'period_start',
                'period_end',
            ], 'pis_unique_snapshot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_intelligence_snapshots');
    }
};