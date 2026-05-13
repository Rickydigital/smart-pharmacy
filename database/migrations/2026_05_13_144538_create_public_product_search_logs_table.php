<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_product_search_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('query')->nullable();
            $table->string('source')->default('smart_app');

            $table->string('result_status')->nullable();
            $table->unsignedInteger('results_count')->default(0);

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index(['query', 'created_at']);
            $table->index('result_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_product_search_logs');
    }
};