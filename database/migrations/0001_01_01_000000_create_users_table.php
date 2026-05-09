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
        /*
        |--------------------------------------------------------------------------
        | Pharmacies
        |--------------------------------------------------------------------------
        | Main pharmacy account. For now, one pharmacy will be seeded first.
        */
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            $table->string('logo_path')->nullable();

            $table->string('status')->default('active'); 
            // active, inactive, suspended

            $table->timestamps();

            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        | Even if we start with one branch, users/sales/purchases should belong to a branch.
        */
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained('pharmacies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code');

            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['pharmacy_id', 'code']);
            $table->index(['pharmacy_id', 'is_active']);
        });

        /*
        |--------------------------------------------------------------------------
        | Pharmacy Settings
        |--------------------------------------------------------------------------
        | Business behavior for the pharmacy.
        */
        Schema::create('pharmacy_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained('pharmacies')
                ->cascadeOnDelete();

            $table->string('currency', 10)->default('TZS');

            $table->string('selling_mode')->default('retail_and_wholesale');
            // retail_only, wholesale_only, retail_and_wholesale

            $table->unsignedInteger('expiry_warning_days')->default(30);
            $table->boolean('block_expired_stock')->default(true);

            $table->boolean('require_prescription_upload')->default(false);
            $table->boolean('require_pharmacist_approval')->default(false);

            $table->string('receipt_footer')->nullable();

            $table->timestamps();

            $table->unique('pharmacy_id');
        });

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        | Staff accounts. Supports username/email login.
        */
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained('pharmacies')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->string('username')->unique();

            /*
            |--------------------------------------------------------------------------
            | Email
            |--------------------------------------------------------------------------
            | Email is nullable because cashiers/storekeepers may use username only.
            | Owner/Admin should have email, but that can be validated from forms.
            */
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('phone')->nullable();

            $table->string('password');

            $table->string('status')->default('active');
            // active, inactive, blocked

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->index(['pharmacy_id', 'branch_id']);
            $table->index(['pharmacy_id', 'status']);
            $table->index('username');
        });

        /*
        |--------------------------------------------------------------------------
        | Password Reset Tokens
        |--------------------------------------------------------------------------
        | Since staff may use username only, this remains email-based for owners/admins.
        | Staff password reset can be handled by Owner/Admin from user management.
        */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        | Required when SESSION_DRIVER=database.
        */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('pharmacy_settings');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('pharmacies');
    }
};