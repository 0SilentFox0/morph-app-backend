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
        Schema::create('profile_view_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('viewed_user_id');
            $table->uuid('viewer_user_id')->nullable();
            $table->string('viewer_ip_hash', 64)->nullable();
            $table->timestamp('viewed_at')->useCurrent();
        });

        Schema::create('analytics_cache', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cache_key', 255)->unique();
            $table->json('payload');
            $table->timestamp('computed_at')->useCurrent();
            $table->timestamp('expires_at')->useCurrent();
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('key', 64);
            $table->timestamp('earned_at')->useCurrent();
            $table->json('payload')->nullable();

            $table->unique(['user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('analytics_cache');
        Schema::dropIfExists('profile_view_events');
    }
};
