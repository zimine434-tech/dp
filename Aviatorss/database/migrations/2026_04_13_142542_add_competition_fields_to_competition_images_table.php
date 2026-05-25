<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competition_images', function (Blueprint $table) {
            $table->foreignId('competition_id')->after('id')->constrained('competitions')->cascadeOnDelete();
            $table->string('path', 500)->after('competition_id');
            $table->unsignedInteger('size_bytes')->nullable()->after('path');

            $table->index('competition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_images', function (Blueprint $table) {
            $table->dropIndex(['competition_id']);
            $table->dropConstrainedForeignId('competition_id');
            $table->dropColumn(['path', 'size_bytes']);
        });
    }
};
