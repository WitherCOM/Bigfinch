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
        Schema::create('file_transaction', function (Blueprint $table) {
            $table->foreignUuid('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['file_id', 'transaction_id']);
            $table->unique('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_transaction');
    }
};
