<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('adjustment_no')->unique();
            $table->date('adjustment_date');

            $table->string('adjustment_type')->default('correction');
            // damage, expiry, physical_count, loss, found_stock, correction

            $table->string('status')->default('draft');
            // draft, approved, rejected, cancelled

            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('total_quantity_base_units')->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id', 'adjustment_date']);
            $table->index(['pharmacy_id', 'status']);
            $table->index(['pharmacy_id', 'adjustment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};