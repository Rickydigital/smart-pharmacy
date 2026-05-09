<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();

            $table->string('return_no')->unique();
            $table->date('return_date');

            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);

            $table->string('refund_method')->default('cash');
            // cash, mobile_money, card, bank, credit_note, no_refund

            $table->string('status')->default('draft');
            // draft, approved, rejected, cancelled

            $table->string('return_type')->default('customer_return');
            // customer_return, correction, damaged_return

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id', 'return_date']);
            $table->index(['pharmacy_id', 'sale_id']);
            $table->index(['pharmacy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};