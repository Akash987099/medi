<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'api_token_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('api_token_hash', 64)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'api_token_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_api_token_hash_unique');
                $table->dropColumn('api_token_hash');
            });
        }
    }
};
