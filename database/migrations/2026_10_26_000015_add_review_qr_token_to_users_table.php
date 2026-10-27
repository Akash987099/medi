<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'review_qr_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('review_qr_token', 64)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'review_qr_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['review_qr_token']);
                $table->dropColumn('review_qr_token');
            });
        }
    }
};
