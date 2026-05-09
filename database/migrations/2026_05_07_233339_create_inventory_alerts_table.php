<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('alert_no')->unique();

            $table->string('alert_type');
            // low_stock, expiring_soon, expired, out_of_stock

            $table->string('severity')->default('medium');
            // low, medium, high, critical

            $table->string('title');
            $table->text('message');

            $table->unsignedInteger('available_quantity_base_units')->default(0);
            $table->unsignedInteger('threshold_quantity_base_units')->nullable();

            $table->date('expiry_date')->nullable();
            $table->integer('days_to_expiry')->nullable();

            $table->string('status')->default('open');
            // open, read, resolved, ignored

            $table->timestamp('notified_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('channels_sent')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'alert_type']);
            $table->index(['pharmacy_id', 'status']);
            $table->index(['pharmacy_id', 'severity']);
            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};