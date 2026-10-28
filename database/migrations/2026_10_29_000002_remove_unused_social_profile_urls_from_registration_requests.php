<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_partner_registration_requests', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_url',
                'youtube_url',
                'facebook_url',
                'linkedin_url',
                'other_social_url',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('care_partner_registration_requests', function (Blueprint $table) {
            $table->string('instagram_url', 2048)->nullable();
            $table->string('youtube_url', 2048)->nullable();
            $table->string('facebook_url', 2048)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->string('other_social_url', 2048)->nullable();
        });
    }
};
