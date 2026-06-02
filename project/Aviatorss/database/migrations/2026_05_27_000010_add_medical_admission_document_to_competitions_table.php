<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('medical_admission_document_path')->nullable()->after('form_regulation_text');
            $table->string('medical_admission_document_original_name')->nullable()->after('medical_admission_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn([
                'medical_admission_document_path',
                'medical_admission_document_original_name',
            ]);
        });
    }
};