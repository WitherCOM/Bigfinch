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
        Schema::dropIfExists('merchants');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->json('search_keys')->default('[]');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('expense_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('income_category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }
};
