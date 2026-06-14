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
        Schema::create('media_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_user_id')->nullable();
            $table->enum('purpose', ['avatar', 'exercise_video', 'chat_media', 'data_export', 'progress_export', 'other']);
            $table->string('mime', 100);
            $table->bigInteger('size_bytes');
            $table->string('s3_bucket', 255);
            $table->string('s3_key', 512);
            $table->string('original_name', 255)->nullable();
            $table->enum('status', ['pending', 'ready', 'failed'])->default('pending');
            $table->json('thumbnails')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_user_id');
            $table->index('status');
            $table->index(['purpose', 'created_at']);
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
