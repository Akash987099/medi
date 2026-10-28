<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_api_domains')) {
            Schema::create('user_api_domains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('domain', 253);
                $table->string('domain_hash', 64)->unique();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });

            return;
        }

        if (! Schema::hasColumn('user_api_domains', 'domain_hash')) {
            Schema::table('user_api_domains', function (Blueprint $table) {
                $table->string('domain_hash', 64)->nullable();
            });

            DB::table('user_api_domains')
                ->whereNull('domain_hash')
                ->orderBy('id')
                ->chunkById(100, function ($domains): void {
                    foreach ($domains as $domain) {
                        DB::table('user_api_domains')
                            ->where('id', $domain->id)
                            ->update(['domain_hash' => hash('sha256', $domain->domain)]);
                    }
                });

            Schema::table('user_api_domains', function (Blueprint $table) {
                $table->unique('domain_hash');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_api_domains');
    }
};
