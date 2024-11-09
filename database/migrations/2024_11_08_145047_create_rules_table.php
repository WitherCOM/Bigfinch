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
        Schema::create('rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Enum: RuleType

            // Lookup fields are AND-ed together
            $table->string('description_lookup')->nullable();
            $table->foreignUuid('merchant_id_lookup')->nullable()
                ->references('id')->on('merchants')->cascadeOnDelete();
            $table->foreignUuid('category_id_lookup')->nullable()
                ->references('id')->on('categories')->cascadeOnDelete();
            $table->foreignUuid('currency_id_lookup')->nullable()
                ->references('id')->on('currencies')->cascadeOnDelete();
            $table->enum('direction_lookup',array_column(\App\Enums\Direction::cases(), 'value'))->nullable();
            $table->double('min_value_lookup')->nullable();
            $table->double('max_value_lookup')->nullable();

            // RuleType: CATEGORY unique
            $table->foreignUuid('target_id')->nullable()
                ->references('id')->on('categories')->cascadeOnDelete(); // Used for category rule type to set this category
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
