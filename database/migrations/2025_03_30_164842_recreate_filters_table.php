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
            $table->string('name')->nullable();


            $table->dateTime('from')->nullable();
            $table->dateTime('to')->nullable();
            $table->string('tag')->nullable();
            $table->string('merchant')->nullable();
            $table->string('direction')->nullable();
            $table->double('min_value')->nullable();
            $table->double('max_value')->nullable();
            $table->string('currency')->nullable();
            $table->string('description')->nullable();
            $table->string('flag')->nullable();

            $table->string('action');
            $table->json('action_parameters')->default('[]');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
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
