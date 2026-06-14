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
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->integer('estimated_duration_min')->nullable();
            $table->uuid('cover_file_id')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['trainer_id', 'archived_at']);
        });

        Schema::create('program_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('exercise_id')->nullable();
            $table->integer('order');
            $table->integer('sets');
            $table->integer('reps');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->integer('rest_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->string('name_snapshot', 255);
            $table->timestamp('created_at')->nullable();

            $table->unique(['program_id', 'order']);
        });

        Schema::create('program_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('media_file_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('order');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('program_likes', function (Blueprint $table) {
            $table->uuid('program_id');
            $table->uuid('user_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['program_id', 'user_id']);
        });

        Schema::create('client_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('program_id')->nullable();
            $table->json('program_snapshot');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['client_id', 'removed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_programs');
        Schema::dropIfExists('program_likes');
        Schema::dropIfExists('program_videos');
        Schema::dropIfExists('program_exercises');
        Schema::dropIfExists('programs');
    }
};
