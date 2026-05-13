<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('street_id')
                ->nullable()
                ->after('address')
                ->constrained('streets')
                ->nullOnDelete();

            $table->decimal('latitude', 10, 7)->nullable()->after('street_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            $table->string('whatsapp')->nullable()->after('phone');
            $table->time('opens_at')->nullable()->after('whatsapp');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->boolean('is_24_hours')->default(false)->after('closes_at');

            $table->timestamp('last_location_synced_at')->nullable()->after('is_24_hours');

            $table->index('street_id');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('street_id');
            $table->dropColumn([
                'latitude',
                'longitude',
                'whatsapp',
                'opens_at',
                'closes_at',
                'is_24_hours',
                'last_location_synced_at',
            ]);
        });
    }
};