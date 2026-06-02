<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('max_bot_subscribers', function (Blueprint $table) {
            $table->json('training_sport_ids')->nullable()->after('chat_id');
            $table->json('competition_sport_ids')->nullable()->after('training_sport_ids');
        });

        // Backfill: old `sport_ids` -> `training_sport_ids`
        if (Schema::hasColumn('max_bot_subscribers', 'sport_ids')) {
            DB::table('max_bot_subscribers')->update([
                'training_sport_ids' => DB::raw('sport_ids'),
            ]);

            Schema::table('max_bot_subscribers', function (Blueprint $table) {
                $table->dropColumn('sport_ids');
            });
        }
    }

    public function down(): void
    {
        Schema::table('max_bot_subscribers', function (Blueprint $table) {
            // Re-create old column for rollback compatibility
            if (! Schema::hasColumn('max_bot_subscribers', 'sport_ids')) {
                $table->json('sport_ids')->nullable();
            }
        });

        // Restore `sport_ids` from `training_sport_ids` if present
        if (Schema::hasColumn('max_bot_subscribers', 'training_sport_ids')) {
            DB::table('max_bot_subscribers')->update([
                'sport_ids' => DB::raw('training_sport_ids'),
            ]);
        }

        Schema::table('max_bot_subscribers', function (Blueprint $table) {
            if (Schema::hasColumn('max_bot_subscribers', 'training_sport_ids')) {
                $table->dropColumn('training_sport_ids');
            }
            if (Schema::hasColumn('max_bot_subscribers', 'competition_sport_ids')) {
                $table->dropColumn('competition_sport_ids');
            }
        });
    }
};

