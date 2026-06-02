<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->date('issued_at')->nullable()->after('form_issued');
            $table->date('submitted_at')->nullable()->after('form_status');
        });

        DB::table('competition_forms')
            ->where('form_issued', true)
            ->whereNull('issued_at')
            ->update(['issued_at' => DB::raw('DATE(created_at)')]);

        DB::table('competition_forms')
            ->where('form_status', 'submitted')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('DATE(updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropColumn(['issued_at', 'submitted_at']);
        });
    }
};
