<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * С полем team_role сразу (раньше добавлялось отдельной миграцией).
     */
    public function up(): void
    {
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['student', 'teacher'])->nullable();
            $table->string('team_role', 20)->default('participant');
            $table->timestamps();
            $table->primary(['competition_id', 'user_id']);
            $table->foreign('competition_id')->references('id')->on('competitions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};
