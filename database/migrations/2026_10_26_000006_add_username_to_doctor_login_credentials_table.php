<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('doctor_login_credentials', 'username')) {
            Schema::table('doctor_login_credentials', function (Blueprint $table) {
                $table->string('username', 8)->nullable()->unique()->after('user_id');
            });
        }

        DB::table('doctor_login_credentials')
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunkById(100, function ($credentials): void {
                foreach ($credentials as $credential) {
                    $user = DB::table('users')
                        ->where('id', $credential->user_id)
                        ->first(['id', 'username']);

                    if (!$user) {
                        throw new RuntimeException(
                            "Cannot backfill login credential {$credential->id}: the linked user does not exist."
                        );
                    }

                    $username = $user->username;

                    if (!$username) {
                        do {
                            $username = Str::lower(Str::random(8));
                        } while (DB::table('users')->where('username', $username)->exists());

                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['username' => $username]);
                    }

                    DB::table('doctor_login_credentials')
                        ->where('id', $credential->id)
                        ->update(['username' => $username]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctor_login_credentials', 'username')) {
            Schema::table('doctor_login_credentials', function (Blueprint $table) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            });
        }
    }
};
