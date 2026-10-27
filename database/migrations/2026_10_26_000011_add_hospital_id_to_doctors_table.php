<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('doctors', 'hospital_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('category_id');
                $table->index('hospital_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctors', 'hospital_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropIndex(['hospital_id']);
                $table->dropColumn('hospital_id');
            });
        }
    }
};
