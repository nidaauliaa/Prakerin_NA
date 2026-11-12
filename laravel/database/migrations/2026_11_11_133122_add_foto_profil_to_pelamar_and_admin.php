<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Foto profil yang bisa diubah sendiri lewat menu foto di header (pelamar & admin). */
    public function up(): void
    {
        Schema::table('pelamar', function (Blueprint $table) {
            $table->string('foto_profil', 255)->nullable()->after('password');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('foto_profil', 255)->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('pelamar', fn (Blueprint $t) => $t->dropColumn('foto_profil'));
        Schema::table('admins', fn (Blueprint $t) => $t->dropColumn('foto_profil'));
    }
};
