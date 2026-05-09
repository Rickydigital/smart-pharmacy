<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();

            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->cascadeOnDelete();

            $table->decimal('quantity', 15, 2)->default(0);
            $table->unsignedInteger('quantity_in_base_units')->default(1);
            $table->unsignedInteger('total_base_units')->default(0);

            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);

            $table->decimal('cost_per_base_unit', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('profit_reversed', 15, 2)->default(0);

            $table->string('condition')->default('sellable');
            // sellable, damaged, expired, opened

            $table->boolean('restore_to_inventory')->default(true);

            $table->json('inventory_allocations')->nullable();

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'sale_id']);
            $table->index(['pharmacy_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};