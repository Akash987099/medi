<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'api_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('api_token')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'api_token_encrypted')) {
            DB::table('users')
                ->whereNull('api_token')
                ->whereNotNull('api_token_encrypted')
                ->chunkById(100, function ($users): void {
                    foreach ($users as $user) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['api_token' => $user->api_token_encrypted]);
                    }
                });
        }

        if (Schema::hasColumn('users', 'api_token_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_api_token_hash_unique');
                $table->dropColumn('api_token_hash');
            });
        }

        if (Schema::hasColumn('users', 'api_token_encrypted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('api_token_encrypted');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'api_token_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('api_token_hash', 64)->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('users', 'api_token_encrypted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('api_token_encrypted')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'api_token')) {
            DB::table('users')
                ->whereNotNull('api_token')
                ->chunkById(100, function ($users): void {
                    foreach ($users as $user) {
                        $token = Crypt::decryptString($user->api_token);
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update([
                                'api_token_hash' => hash('sha256', $token),
                                'api_token_encrypted' => $user->api_token,
                            ]);
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('api_token');
            });
        }
    }
};
