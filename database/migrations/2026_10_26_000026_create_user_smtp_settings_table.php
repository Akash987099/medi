<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('host');
            $table->unsignedSmallInteger('port');
            $table->string('encryption', 8);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_email');
            $table->string('from_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_smtp_settings');
    }
};
