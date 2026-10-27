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
        DB::table('users')
            ->whereNotNull('username')
            ->whereRaw('LENGTH(username) > 8')
            ->orderBy('id')
            ->eachById(function (object $user): void {
                do {
                    $username = Str::lower(Str::random(8));
                } while (DB::table('users')->where('username', $username)->exists());

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 80)->nullable()->change();
        });
    }
};
