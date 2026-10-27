<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('role')->nullable()->after('username');
            });
        }

        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'user_id')) {
            DB::table('doctors')
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->each(function ($userId): void {
                    DB::table('users')->where('id', $userId)->update(['role' => 2]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
