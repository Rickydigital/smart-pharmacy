<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('expense_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('expense_no')->unique();

            $table->date('expense_date');

            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);

            $table->string('payment_method')->default('cash');
            // cash, mobile_money, card, bank

            $table->string('status')->default('paid');
            // paid, voided

            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('voided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'expense_category_id']);
            $table->index(['pharmacy_id', 'expense_date']);
            $table->index(['pharmacy_id', 'payment_method']);
            $table->index(['pharmacy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};