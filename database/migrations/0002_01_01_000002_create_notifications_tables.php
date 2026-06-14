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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 512)->unique();
            $table->enum('platform', ['ios', 'android']);
            $table->string('device_label', 255)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recipient_user_id');
            $table->string('type', 64);
            $table->string('title', 255)->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('source_type', 64)->nullable();
            $table->uuid('source_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['recipient_user_id', 'created_at']);
            $table->index('recipient_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('device_tokens');
    }
};
