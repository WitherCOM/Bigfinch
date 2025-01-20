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
        Schema::dropIfExists('filters');
        Schema::create('filters', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('filter_rule');
            $table->string('action_rule');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filters');
    }
};
