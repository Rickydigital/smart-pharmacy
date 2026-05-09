<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('quantity_in_base_units')->default(1);

            $table->boolean('can_purchase')->default(true);
            $table->boolean('can_sell_retail')->default(true);
            $table->boolean('can_sell_wholesale')->default(false);

            $table->boolean('is_base')->default(false);
            $table->boolean('is_default_sale_unit')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
            $table->index(['pharmacy_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
