<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hospital_login_credentials')) {
            Schema::create('hospital_login_credentials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hospital_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('username', 8)->unique();
                $table->text('password');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_login_credentials');
    }
};
