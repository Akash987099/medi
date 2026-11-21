<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_patients', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('name');
            $table->string('email', 255)->nullable()->after('phone');
        });

        DB::table('user_patients')->whereNotNull('contact')->update(['phone' => DB::raw('contact')]);

        Schema::table('user_patients', function (Blueprint $table) {
            $table->dropColumn('contact');
        });
    }

    public function down(): void
    {
        Schema::table('user_patients', function (Blueprint $table) {
            $table->string('contact', 50)->nullable()->after('name');
        });

        Schema::table('user_patients', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email']);
        });
    }
};
