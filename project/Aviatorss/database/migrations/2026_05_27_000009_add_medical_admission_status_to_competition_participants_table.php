<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_participants', function (Blueprint $table) {
            $table->string('medical_admission_status', 20)
                ->default('pending')
                ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('competition_participants', function (Blueprint $table) {
            $table->dropColumn('medical_admission_status');
        });
    }
};
