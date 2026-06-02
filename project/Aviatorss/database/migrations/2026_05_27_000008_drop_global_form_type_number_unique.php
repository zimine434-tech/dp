<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->index('form_type_id', 'competition_forms_form_type_id_index');
        });

        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropUnique('competition_forms_type_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->unique(['form_type_id', 'form_number'], 'competition_forms_type_number_unique');
        });

        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropIndex('competition_forms_form_type_id_index');
        });
    }
};
