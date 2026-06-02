<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competition_results', function (Blueprint $table) {
            $table
                ->enum('result_type', ['personal', 'team'])
                ->default('team')
                ->after('place');

            $table->foreignId('user_id')->nullable()->after('competitions_id')->constrained('users')->nullOnDelete();
            $table->unique(['competitions_id', 'user_id'], 'competition_results_competition_user_unique');
        });

        // Backfill existing rows: they are team results
        DB::table('competition_results')->whereNull('result_type')->update(['result_type' => 'team']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_results', function (Blueprint $table) {
            $table->dropUnique('competition_results_competition_user_unique');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('result_type');

            // NOTE: teams_id stays unchanged
        });
    }
};
