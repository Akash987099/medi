<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('patient_name', 120);
            $table->string('phone', 30);
            $table->string('email', 191)->nullable();
            $table->dateTime('appointment_at');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('reason', 250);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->index(['user_id', 'appointment_at']);
            $table->index(['user_id', 'status', 'appointment_at']);
            $table->index(['doctor_id', 'appointment_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_appointments');
    }
};
