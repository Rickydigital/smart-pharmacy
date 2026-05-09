<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();

            $table->foreignId('source_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('destination_branch_id')->constrained('branches')->cascadeOnDelete();

            $table->string('transfer_no')->unique();
            $table->date('transfer_date');

            $table->string('status')->default('draft');
            // draft, approved, dispatched, received, rejected, cancelled

            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('total_quantity_base_units')->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'source_branch_id', 'transfer_date'], 'st_pharm_source_date_idx');
            $table->index(['pharmacy_id', 'destination_branch_id', 'transfer_date'], 'st_pharm_dest_date_idx');
            $table->index(['pharmacy_id', 'status'], 'st_pharm_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};