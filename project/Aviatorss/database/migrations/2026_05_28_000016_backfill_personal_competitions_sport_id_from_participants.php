<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For personal competitions created before we started auto-filling sport_id:
        // derive sport_id from participant team_id -> teams.sport_id.
        // If multiple sports exist (shouldn't), we pick MIN(sport_id) deterministically.
        DB::statement("
            UPDATE competitions c
            JOIN (
                SELECT
                    cp.competition_id AS competition_id,
                    MIN(t.sport_id) AS sport_id
                FROM competition_participants cp
                JOIN teams t ON t.id = cp.team_id
                WHERE cp.team_id IS NOT NULL
                  AND t.sport_id IS NOT NULL
                GROUP BY cp.competition_id
            ) x ON x.competition_id = c.id
            SET c.sport_id = x.sport_id
            WHERE (c.result_type = 'personal' OR c.result_type IS NULL)
              AND c.sport_id IS NULL
        ");
    }

    public function down(): void
    {
        // No safe rollback: we can't distinguish backfilled vs user-provided sport_id.
    }
};

