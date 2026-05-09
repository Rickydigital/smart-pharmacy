<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sale_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->integer('quantity')->default(1);
            $table->integer('quantity_in_base_units')->default(1);
            $table->integer('total_base_units')->default(1);

            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_discount', 15, 2)->default(0);
            $table->decimal('line_tax', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->decimal('cost_per_base_unit', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('profit_amount', 15, 2)->default(0);

            $table->json('inventory_allocations')->nullable();
            // stores inventory rows consumed:
            // [{"inventory_id":1,"quantity_base_units":20,"unit_cost_base":500}]

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'sale_id']);
            $table->index(['pharmacy_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};