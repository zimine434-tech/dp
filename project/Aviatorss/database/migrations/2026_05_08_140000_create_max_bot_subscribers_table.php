<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('max_bot_subscribers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_user_id')->unique();
            $table->unsignedBigInteger('chat_id')->nullable();
            $table->json('sport_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('max_bot_subscribers');
    }
};
