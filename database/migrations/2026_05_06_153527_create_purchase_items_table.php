<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();

            $table->integer('quantity')->default(1);
            $table->integer('quantity_in_base_units')->default(1);
            $table->integer('total_base_units')->default(1);

            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_discount', 15, 2)->default(0);
            $table->decimal('line_tax', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['pharmacy_id', 'purchase_id']);
            $table->index(['pharmacy_id', 'product_id']);
            $table->index(['batch_no']);
            $table->index(['expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};