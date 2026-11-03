<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleName = config('filament-shield.super_admin.name');
        $adminIds = DB::table('users')->where('can_manage_settings', true)->pluck('id');

        if ($adminIds->isNotEmpty()) {
            DB::table('roles')->insertOrIgnore([
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');

            foreach ($adminIds as $adminId) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $adminId,
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_manage_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_manage_settings')->default(false);
        });
    }
};
