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
        Schema::create('session_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->json('template');
            $table->json('recurrence_rule');
            $table->string('timezone', 64);
            $table->date('materialized_until')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('training_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->string('title', 255);
            $table->string('type', 50)->nullable();
            $table->timestamp('start_at')->useCurrent();
            $table->timestamp('end_at')->useCurrent();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'canceled', 'no_show'])->default('planned');
            $table->timestamp('status_changed_at')->nullable();
            $table->string('cancellation_reason', 64)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('program_id')->nullable();
            $table->uuid('client_package_id')->nullable();
            $table->uuid('series_id')->nullable();
            $table->boolean('series_overridden')->default(false);
            $table->string('google_event_id', 255)->nullable();
            $table->string('google_event_etag', 255)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();

            $table->index(['trainer_id', 'start_at']);
            $table->index(['status', 'start_at']);
        });

        Schema::create('session_participants', function (Blueprint $table) {
            $table->uuid('session_id');
            $table->uuid('client_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['session_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_participants');
        Schema::dropIfExists('training_sessions');
        Schema::dropIfExists('session_series');
    }
};
