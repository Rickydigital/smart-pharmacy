<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();

            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();

            $table->foreignId('source_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('destination_branch_id')->constrained('branches')->cascadeOnDelete();

            $table->foreignId('source_inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('destination_inventory_id')->nullable()->constrained('inventories')->nullOnDelete();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();

            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete();

            $table->decimal('quantity', 15, 2)->default(0);
            $table->unsignedInteger('quantity_in_base_units')->default(1);
            $table->unsignedInteger('quantity_base_units');
            $table->decimal('unit_cost_base', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->unsignedInteger('source_balance_before_base_units')->default(0);
            $table->unsignedInteger('source_balance_after_base_units')->default(0);

            $table->unsignedInteger('destination_balance_before_base_units')->default(0);
            $table->unsignedInteger('destination_balance_after_base_units')->default(0);

            $table->timestamps();

            $table->index(['pharmacy_id', 'source_branch_id']);
            $table->index(['pharmacy_id', 'destination_branch_id']);
            $table->index(['pharmacy_id', 'product_id']);
            $table->index(['source_inventory_id']);
            $table->index(['destination_inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};