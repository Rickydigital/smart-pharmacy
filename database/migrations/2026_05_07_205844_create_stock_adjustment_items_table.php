<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();

            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('direction');
            // in, out

            $table->unsignedInteger('quantity_base_units');
            $table->decimal('unit_cost_base', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->unsignedInteger('balance_before_base_units')->default(0);
            $table->unsignedInteger('balance_after_base_units')->default(0);

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'product_id']);
            $table->index(['pharmacy_id', 'inventory_id']);
            $table->index(['direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};