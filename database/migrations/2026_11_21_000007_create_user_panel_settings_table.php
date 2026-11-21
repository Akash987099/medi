<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_panel_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('primary_color', 7)->default('#54745d');
            $table->string('background_color', 7)->default('#f7f6f1');
            $table->string('text_color', 7)->default('#26382f');
            $table->string('accent_color', 7)->default('#bd795e');
            $table->string('sidebar_color', 7)->default('#ffffff');
            $table->string('panel_color', 7)->default('#fffefa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_panel_settings');
    }
};
