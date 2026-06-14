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
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('last_message_at')->nullable();
            $table->uuid('last_message_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->uuid('conversation_id');
            $table->uuid('user_id');
            $table->uuid('last_read_message_id')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();

            $table->primary(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id')->nullable();
            $table->text('body')->nullable();
            $table->json('media_file_ids')->nullable();
            $table->uuid('client_message_id');
            $table->timestamp('sent_at')->useCurrent();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();

            $table->index(['conversation_id', 'sent_at']);
            $table->unique(['conversation_id', 'client_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
