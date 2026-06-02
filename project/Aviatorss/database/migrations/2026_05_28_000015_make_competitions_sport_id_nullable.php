<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK (name may differ between environments)
        $rows = DB::select(
            "select CONSTRAINT_NAME
             from information_schema.KEY_COLUMN_USAGE
             where TABLE_SCHEMA = database()
               and TABLE_NAME = 'competitions'
               and COLUMN_NAME = 'sport_id'
               and REFERENCED_TABLE_NAME is not null"
        );

        foreach ($rows as $row) {
            $name = $row->CONSTRAINT_NAME ?? null;
            if (is_string($name) && $name !== '') {
                DB::statement("ALTER TABLE `competitions` DROP FOREIGN KEY `{$name}`");
            }
        }

        // Make nullable
        DB::statement("ALTER TABLE `competitions` MODIFY `sport_id` BIGINT UNSIGNED NULL");

        // Re-add FK
        Schema::table('competitions', function ($table) {
            $table->foreign('sport_id')->references('id')->on('sports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop FK (name may differ)
        $rows = DB::select(
            "select CONSTRAINT_NAME
             from information_schema.KEY_COLUMN_USAGE
             where TABLE_SCHEMA = database()
               and TABLE_NAME = 'competitions'
               and COLUMN_NAME = 'sport_id'
               and REFERENCED_TABLE_NAME is not null"
        );

        foreach ($rows as $row) {
            $name = $row->CONSTRAINT_NAME ?? null;
            if (is_string($name) && $name !== '') {
                DB::statement("ALTER TABLE `competitions` DROP FOREIGN KEY `{$name}`");
            }
        }

        // Make NOT NULL again (will fail if there are NULLs)
        DB::statement("ALTER TABLE `competitions` MODIFY `sport_id` BIGINT UNSIGNED NOT NULL");

        Schema::table('competitions', function ($table) {
            $table->foreign('sport_id')->references('id')->on('sports')->cascadeOnDelete();
        });
    }
};

