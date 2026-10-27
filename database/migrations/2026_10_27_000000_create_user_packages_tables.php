<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('user_package_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_id')->constrained('user_packages')->cascadeOnDelete();
            $table->foreignId('user_service_id')->constrained('user_services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_package_id', 'user_service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_package_services');
        Schema::dropIfExists('user_packages');
    }
};
