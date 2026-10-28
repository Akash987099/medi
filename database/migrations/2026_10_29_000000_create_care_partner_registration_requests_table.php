<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_partner_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('plan', 40);
            $table->string('practice_type', 30);
            $table->string('practice_name', 160);
            $table->string('specialty', 160)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->text('address');
            $table->string('city', 120);
            $table->string('state', 120);
            $table->string('postal_code', 20);
            $table->string('contact_name', 120);
            $table->string('email', 191);
            $table->string('phone', 30);
            $table->text('message')->nullable();
            $table->timestamp('consented_at');
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_partner_registration_requests');
    }
};
