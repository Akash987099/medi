<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_book_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_book_id')->constrained('user_books')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->string('caption', 220)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'user_book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_book_gallery_images');
    }
};
