<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('site_name', 160);
            $table->string('tagline')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->text('address')->nullable();
            $table->string('header_logo_path', 2048)->nullable();
            $table->string('footer_logo_path', 2048)->nullable();
            $table->text('footer_text')->nullable();
            $table->string('copyright_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_website_settings');
    }
};
