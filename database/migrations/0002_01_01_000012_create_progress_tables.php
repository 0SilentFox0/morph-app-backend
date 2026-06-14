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
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->enum('metric_type', ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh']);
            $table->decimal('value', 7, 2);
            $table->string('unit', 8);
            $table->timestamp('measured_at')->useCurrent();
            $table->uuid('recorded_by_user_id')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();

            $table->index(['client_id', 'metric_type', 'measured_at']);
        });

        Schema::create('personal_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('exercise_id');
            $table->decimal('weight_kg', 6, 2);
            $table->integer('reps');
            $table->timestamp('achieved_at')->useCurrent();
            $table->uuid('workout_log_set_id')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_records');
        Schema::dropIfExists('body_measurements');
    }
};
