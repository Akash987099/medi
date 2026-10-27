<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_reviews', 'source')) {
            Schema::table('user_reviews', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_reviews') && ! Schema::hasColumn('user_reviews', 'source')) {
            Schema::table('user_reviews', function (Blueprint $table) {
                $table->string('source', 20)->nullable();
            });
        }
    }
};
