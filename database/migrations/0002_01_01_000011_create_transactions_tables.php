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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->uuid('client_id')->nullable();
            $table->uuid('client_package_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UAH');
            $table->enum('method', ['cash', 'transfer', 'card', 'other']);
            $table->enum('status', ['paid', 'pending', 'canceled'])->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['trainer_id', 'paid_at']);
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->timestamp('withdrawn_at')->useCurrent();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('transactions');
    }
};
