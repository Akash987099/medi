<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'api_token_encrypted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('api_token_encrypted')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'api_token_encrypted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('api_token_encrypted');
            });
        }
    }
};
