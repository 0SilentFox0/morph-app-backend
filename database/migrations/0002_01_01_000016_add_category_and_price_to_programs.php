<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('difficulty');
            $table->decimal('price', 10, 2)->nullable()->after('category');
            $table->string('price_currency', 3)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['category', 'price', 'price_currency']);
        });
    }
};
