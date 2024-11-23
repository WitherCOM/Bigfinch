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
        Schema::table('merchants',function (Blueprint $table) {
            $table->foreignUuid('expense_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('income_category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchants',function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_category_id');
            $table->dropConstrainedForeignId('income_category_id');
            $table->dropColumn(['expense_category_id','income_category_id']);
        });
    }
};
