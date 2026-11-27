<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'opening_time')) {
                $table->time('opening_time')->nullable()->after('experience_years');
            }

            if (! Schema::hasColumn('doctors', 'closing_time')) {
                $table->time('closing_time')->nullable()->after('opening_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('doctors', 'opening_time')) {
                $columns[] = 'opening_time';
            }

            if (Schema::hasColumn('doctors', 'closing_time')) {
                $columns[] = 'closing_time';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
