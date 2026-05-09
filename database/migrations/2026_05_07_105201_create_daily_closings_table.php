<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('cashier_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('closing_date');

            $table->decimal('cash_sales_amount', 15, 2)->default(0);
            $table->decimal('mobile_money_sales_amount', 15, 2)->default(0);
            $table->decimal('card_sales_amount', 15, 2)->default(0);
            $table->decimal('bank_sales_amount', 15, 2)->default(0);
            $table->decimal('credit_sales_amount', 15, 2)->default(0);

            $table->decimal('total_sales_amount', 15, 2)->default(0);
            $table->decimal('total_discount_amount', 15, 2)->default(0);

            $table->decimal('cash_expenses_amount', 15, 2)->default(0);
            $table->decimal('other_expenses_amount', 15, 2)->default(0);
            $table->decimal('total_expenses_amount', 15, 2)->default(0);

            $table->decimal('expected_cash_amount', 15, 2)->default(0);
            $table->decimal('counted_cash_amount', 15, 2)->default(0);
            $table->decimal('difference_amount', 15, 2)->default(0);

            $table->string('closing_result')->default('balanced');
            // balanced, short, over

            $table->string('status')->default('draft');
            // draft, submitted, verified, rejected

            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['pharmacy_id', 'branch_id', 'cashier_id', 'closing_date'], 'daily_closing_unique_day');
            $table->index(['pharmacy_id', 'branch_id', 'closing_date']);
            $table->index(['pharmacy_id', 'cashier_id', 'closing_date']);
            $table->index(['pharmacy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};