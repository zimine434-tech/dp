<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Итоговая схема: без is_active, с id_out, индекс (team_id, user_id) без unique (история участия).
     */
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_adding')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('joined_at')->useCurrent();
            $table->dateTime('out')->nullable();
            $table->foreignId('id_out')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type_user', ['coach', 'capitan', 'member'])->nullable();
            $table->timestamps();
            $table->index(['team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
