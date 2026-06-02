<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_forms', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('user_id');

            // Вид формы (например: "Костюм спортивный", "Форма ...")
            $table->string('form_view', 255)->nullable();
            // Номер формы
            $table->string('form_number', 50)->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'user_id']);

            $table->foreign('competition_id')
                ->references('id')
                ->on('competitions')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_forms');
    }
};

