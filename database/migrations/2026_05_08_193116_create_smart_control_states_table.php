<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_control_states', function (Blueprint $table) {
            $table->id();

            $table->string('project')->nullable();
            $table->string('license_key')->nullable();
            $table->string('instance_id')->nullable();

            $table->boolean('allowed')->default(false);
            $table->boolean('force_logout')->default(false);

            $table->string('tenant_status')->nullable();
            $table->string('license_status')->nullable();
            $table->string('subscription_status')->nullable();

            $table->text('message')->nullable();

            $table->json('features')->nullable();
            $table->json('payload')->nullable();

            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('next_check_after')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();

            $table->timestamps();

            $table->index('allowed');
            $table->index('force_logout');
            $table->index('valid_until');
            $table->index('next_check_after');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_control_states');
    }
};