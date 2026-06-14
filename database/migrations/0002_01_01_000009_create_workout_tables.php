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
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->unique();
            $table->timestamp('started_at')->useCurrent();
            $table->uuid('started_by_user_id')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->uuid('finished_by_user_id')->nullable();
            $table->integer('last_version')->default(0);
            $table->timestamps();
        });

        Schema::create('workout_log_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workout_log_id');
            $table->uuid('exercise_id')->nullable();
            $table->integer('order');
            $table->string('name_snapshot', 255);
            $table->integer('planned_sets')->nullable();
            $table->integer('planned_reps')->nullable();
            $table->decimal('planned_weight_kg', 6, 2)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('workout_log_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workout_log_id');
            $table->uuid('workout_log_exercise_id');
            $table->uuid('exercise_id')->nullable();
            $table->integer('set_index');
            $table->integer('reps');
            $table->decimal('weight_kg', 6, 2)->default(0);
            $table->integer('rest_seconds')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->uuid('actor_user_id');
            $table->boolean('is_pr')->default(false);
            $table->uuid('client_uuid');
            $table->integer('version');
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();

            $table->unique(['workout_log_id', 'client_uuid']);
            $table->index(['exercise_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_log_sets');
        Schema::dropIfExists('workout_log_exercises');
        Schema::dropIfExists('workout_logs');
    }
};
