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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->uuid('user_id')->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('avatar_url', 512)->nullable();
            $table->enum('type', ['personal', 'group', 'online'])->default('personal');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['trainer_id', 'status', 'name']);
            $table->index(['trainer_id', 'user_id']);
            $table->unique(['trainer_id', 'user_id']);
        });

        Schema::create('client_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('trainer_id');
            $table->string('code', 64)->unique();
            $table->string('email', 255);
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invitations');
        Schema::dropIfExists('clients');
    }
};

