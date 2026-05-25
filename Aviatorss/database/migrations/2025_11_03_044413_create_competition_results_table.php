<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();
            $table->string('place', 45)->nullable();
            $table->foreignId('competitions_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('teams_id')->constrained('teams')->cascadeOnDelete();
            $table->boolean('is_archive')->default(false);
            $table->timestamps();

            $table->index('is_archive');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_results');
    }
};
