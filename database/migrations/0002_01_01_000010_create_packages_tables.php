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
        Schema::create('package_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->string('name', 255);
            $table->enum('kind', ['count_based', 'time_based', 'hybrid']);
            $table->integer('sessions_count')->nullable();
            $table->integer('validity_days')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('UAH');
            $table->boolean('auto_renew_default')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('client_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('trainer_id');
            $table->uuid('template_id')->nullable();
            $table->enum('kind', ['count_based', 'time_based', 'hybrid']);
            $table->integer('sessions_count')->nullable();
            $table->integer('remaining_sessions')->nullable();
            $table->integer('validity_days')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['active', 'exhausted', 'expired', 'archived'])->default('active');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('archived_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->uuid('auto_renewed_to_id')->nullable();
            $table->timestamp('expiry_reminded_at')->nullable();
            $table->timestamp('debt_since')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['trainer_id', 'status']);
            $table->index('expires_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_packages');
        Schema::dropIfExists('package_templates');
    }
};
