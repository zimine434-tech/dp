<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            // Уникальный номер формы внутри одного соревнования (NULL допускается многократно)
            $table->unique(['competition_id', 'form_number'], 'competition_forms_competition_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            $table->dropUnique('competition_forms_competition_number_unique');
        });
    }
};

