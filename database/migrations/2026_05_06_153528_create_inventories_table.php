<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('purchase_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();

            $table->integer('received_quantity_base_units')->default(0);
            $table->integer('available_quantity_base_units')->default(0);

            $table->decimal('unit_cost_base', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->string('status')->default('available');
            // available, depleted, expired, blocked

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'branch_id', 'product_id']);
            $table->index(['pharmacy_id', 'batch_no']);
            $table->index(['expiry_date']);
            $table->index(['status']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};