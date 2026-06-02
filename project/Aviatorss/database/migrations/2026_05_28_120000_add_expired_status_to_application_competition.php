<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE application_competition MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('application_competition')
            ->where('status', 'expired')
            ->update(['status' => 'rejected', 'rejection_reason' => 'Соревнование завершено, заявка не была рассмотрена']);

        DB::statement("ALTER TABLE application_competition MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
