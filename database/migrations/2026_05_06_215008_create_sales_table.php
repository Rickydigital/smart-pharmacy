<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('sale_no')->unique();

            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('sale_type')->default('retail');
            // retail, wholesale

            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            $table->string('payment_method')->default('cash');
            // cash, mobile_money, card, bank, credit

            $table->string('payment_status')->default('paid');
            // paid, partial, unpaid

            $table->string('status')->default('completed');
            // completed, cancelled, returned

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('sold_at')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'sale_type']);
            $table->index(['pharmacy_id', 'payment_status']);
            $table->index(['pharmacy_id', 'status']);
            $table->index(['sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};