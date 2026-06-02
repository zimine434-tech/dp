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
            if ($this->indexExists('competition_forms_competition_number_unique')) {
                $table->dropUnique('competition_forms_competition_number_unique');
            }
        });

        // Оставляем одну запись на пару (вид формы + номер), у остальных сбрасываем номер
        $duplicateGroups = DB::table('competition_forms')
            ->select('form_type_id', 'form_number', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('form_type_id')
            ->whereNotNull('form_number')
            ->where('form_number', '!=', '')
            ->groupBy('form_type_id', 'form_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('competition_forms')
                ->where('form_type_id', $group->form_type_id)
                ->where('form_number', $group->form_number)
                ->where('id', '!=', $group->keep_id)
                ->update(['form_number' => null]);
        }

        Schema::table('competition_forms', function (Blueprint $table) {
            if (! $this->indexExists('competition_forms_type_number_unique')) {
                $table->unique(['form_type_id', 'form_number'], 'competition_forms_type_number_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition_forms', function (Blueprint $table) {
            if ($this->indexExists('competition_forms_type_number_unique')) {
                $table->dropUnique('competition_forms_type_number_unique');
            }
            if (! $this->indexExists('competition_forms_competition_number_unique')) {
                $table->unique(['competition_id', 'form_number'], 'competition_forms_competition_number_unique');
            }
        });
    }

    private function indexExists(string $name): bool
    {
        $indexes = Schema::getIndexes('competition_forms');

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $name) {
                return true;
            }
        }

        return false;
    }
};
