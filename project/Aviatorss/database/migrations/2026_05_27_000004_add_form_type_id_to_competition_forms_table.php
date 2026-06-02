<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('form_type_id')->nullable()->after('user_id');
            $table->foreign('form_type_id')
                ->references('id')
                ->on('competition_form_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropForeign(['form_type_id']);
            $table->dropColumn('form_type_id');
        });
    }
};

