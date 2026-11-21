<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE user_email_messages MODIFY status ENUM('draft', 'queued', 'sent', 'failed') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE user_email_messages SET status = 'failed' WHERE status = 'queued'");
        DB::statement("ALTER TABLE user_email_messages MODIFY status ENUM('draft', 'sent', 'failed') NOT NULL DEFAULT 'draft'");
    }
};
