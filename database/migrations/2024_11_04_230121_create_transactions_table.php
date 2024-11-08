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
            $table->string('description');
            $table->double('value');
            $table->dateTime('date');
            $table->string('common_id');
            $table->enum('direction',array_column(\App\Enums\Direction::cases(), 'value'));
            $table->foreignUuid('merchant_id')->nullable()->constrained('merchants')->nullOnDelete();
            $table->foreignUuid('integration_id')->nullable()->constrained('integrations')->nullOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
