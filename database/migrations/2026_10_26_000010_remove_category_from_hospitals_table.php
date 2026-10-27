<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hospitals', 'category_id')) {
            return;
        }

        $categoryForeignKeys = collect(Schema::getForeignKeys('hospitals'))
            ->filter(fn (array $foreignKey): bool => in_array('category_id', $foreignKey['columns'], true))
            ->pluck('name');

        foreach ($categoryForeignKeys as $foreignKey) {
            Schema::table('hospitals', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey);
            });
        }

        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropIndex('hospitals_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('hospitals', 'category_id')) {
            Schema::table('hospitals', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('id');
                $table->index('category_id', 'hospitals_category_id_foreign');
            });
        }
    }
};
