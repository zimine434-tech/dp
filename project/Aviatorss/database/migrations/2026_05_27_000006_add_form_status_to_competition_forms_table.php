<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->string('form_status', 20)->default('pending')->after('form_number');
        });
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropColumn('form_status');
        });
    }
};
