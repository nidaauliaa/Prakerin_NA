<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lamaran', function (Blueprint $table) {
            $table->foreignId('pelamar_id')
                ->nullable()
                ->after('lowongan_id')
                ->constrained('pelamar')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lamaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pelamar_id');
        });
    }
};