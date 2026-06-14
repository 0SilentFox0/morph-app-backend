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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 255)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 32)->unique()->nullable();
            $table->string('password_hash', 255)->nullable();
            $table->string('name', 255);
            $table->string('avatar_url', 512)->nullable();
            $table->enum('role', ['client', 'trainer', 'admin'])->default('client');
            $table->string('timezone', 64)->default('UTC');
            $table->string('locale', 16)->default('uk');
            $table->string('currency', 3)->default('UAH');
            $table->json('notification_preferences')->nullable();
            $table->integer('points')->default(0);

            // Trainer-specific (null for clients)
            $table->string('experience', 255)->nullable();
            $table->json('certifications')->nullable();
            $table->json('training_types')->nullable();
            $table->json('client_types')->nullable();
            $table->json('locations')->nullable();
            $table->time('work_schedule_start')->nullable();
            $table->time('work_schedule_end')->nullable();
            $table->json('work_schedule_days')->nullable();

            // Client-specific (null for trainers)
            $table->json('goals')->nullable();
            $table->enum('fitness_level', ['beginner', 'intermediate', 'advanced'])->nullable();

            // Lifecycle
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->softDeletes();
            $table->timestamp('deletion_scheduled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
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
        Schema::dropIfExists('users');
    }
};
