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
        Schema::table('transactions', function (Blueprint $table) {
            $table->json('tags')->default('[]');
            $table->json('flags');
            $table->dropConstrainedForeignId('merchant_id');
            $table->string('merchant')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['merchant','flags','tags']);
            $table->foreignUuid('merchant_id')->nullable()->constrained('merchants')->nullOnDelete();
        });
    }
};
