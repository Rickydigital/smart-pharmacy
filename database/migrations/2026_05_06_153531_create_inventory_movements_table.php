<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
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

            $table->foreignId('inventory_id')
                ->nullable()
                ->constrained('inventories')
                ->nullOnDelete();

            $table->string('movement_no')->unique();

            $table->string('movement_type');
            // purchase_receive, sale_out, sale_return, purchase_return,
            // adjustment_in, adjustment_out, damage, expired, transfer_in, transfer_out

            $table->string('direction');
            // in, out

            $table->integer('quantity_base_units');

            $table->integer('balance_before_base_units')->default(0);
            $table->integer('balance_after_base_units')->default(0);

            $table->nullableMorphs('source');
            // source_type, source_id: Purchase, Sale, Adjustment, etc.

            $table->text('reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('moved_at')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'branch_id', 'product_id']);
            $table->index(['inventory_id']);
            $table->index(['movement_type']);
            $table->index(['direction']);
            $table->index(['moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};