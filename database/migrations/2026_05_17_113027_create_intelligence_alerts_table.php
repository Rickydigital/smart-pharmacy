<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intelligence_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('alert_type');
            $table->string('severity')->default('info');
            $table->string('title');
            $table->text('message');

            $table->string('status')->default('open');
            $table->json('meta')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(
                ['pharmacy_id', 'branch_id', 'alert_type', 'status'],
                'intel_alerts_scope_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intelligence_alerts');
    }
};