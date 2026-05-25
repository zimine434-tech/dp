<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('training_registrations', 'attended')) {
                $table->dropColumn('attended');
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('training_registrations', 'attended')) {
                $table->boolean('attended')->default(false);
            }
        });
    }
};
