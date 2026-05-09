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
       Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('base_unit_id')->constrained('units')->restrictOnDelete();

            $table->string('name');
            $table->string('code', 80);
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('brand')->nullable();
            $table->string('barcode')->nullable();

            $table->boolean('requires_expiry')->default(true);
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['pharmacy_id', 'code']);
            $table->index(['pharmacy_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
