<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 220);
            $table->string('subtitle', 220)->nullable();
            $table->string('authors', 500);
            $table->string('publisher', 180)->nullable();
            $table->string('isbn', 30)->nullable();
            $table->string('language', 80)->default('English');
            $table->date('publication_date')->nullable();
            $table->enum('format', ['hardback', 'paperback', 'ebook']);
            $table->text('description')->nullable();
            $table->unsignedInteger('inventory_quantity')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('cover_path', 255)->nullable();
            $table->string('ebook_pdf_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'format', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_books');
    }
};
