<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_email_templates', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150); $table->string('subject', 255); $table->longText('body');
            $table->string('image_path', 255)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['user_id', 'is_active']);
        });
        Schema::create('user_email_messages', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_email_template_id')->nullable()->constrained('user_email_templates')->nullOnDelete();
            $table->foreignId('user_patient_id')->nullable()->constrained('user_patients')->nullOnDelete();
            $table->string('recipient_email', 255); $table->string('recipient_name', 150)->nullable();
            $table->string('subject', 255); $table->longText('body'); $table->enum('status', ['draft','sent','failed'])->default('draft');
            $table->text('failure_reason')->nullable(); $table->timestamp('sent_at')->nullable(); $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_email_messages'); Schema::dropIfExists('user_email_templates'); }
};
