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
            $table->boolean('form_issued')->default(false)->after('user_id');
        });

        DB::table('competition_forms')
            ->whereNotNull('form_type_id')
            ->update(['form_issued' => true]);
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropColumn('form_issued');
        });
    }
};
