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
        Schema::create('filters', function (Blueprint $table) {
            $table->uuid('id')->primary();

            //filter stuff
            $table->string('description')->nullable();
            $table->string('direction')->nullable();
            $table->string('merchant')->nullable();
            $table->double('min_value')->nullable();
            $table->double('max_value')->nullable();
            $table->dateTime('from_date')->nullable();
            $table->dateTime('to_date')->nullable();

            $table->string('action');
            $table->string('action_parameter')->nullable();

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
