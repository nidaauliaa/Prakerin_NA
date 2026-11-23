<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lamaran', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama');
            // portal = melamar sendiri lewat portal; internal = didaftarkan langsung oleh HRD/admin
            $table->string('jalur', 10)->default('portal')->after('kode');
        });

        // Peserta jalur internal: tanpa lowongan, dan data yang tidak diisi admin boleh kosong.
        Schema::table('lamaran', function (Blueprint $table) {
            $table->unsignedBigInteger('lowongan_id')->nullable()->change();
            $table->string('no_wa', 20)->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('foto_pas', 255)->nullable()->change();
            $table->string('asal_sekolah', 150)->nullable()->change();
            $table->string('jurusan', 150)->nullable()->change();
        });

        // Akun portal peserta internal dibuat admin dengan sandi awal: wajib diganti saat login pertama.
        Schema::table('pelamar', function (Blueprint $table) {
            $table->boolean('wajib_ganti_password')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('pelamar', fn (Blueprint $t) => $t->dropColumn('wajib_ganti_password'));
        Schema::table('lamaran', fn (Blueprint $t) => $t->dropColumn(['jenis_kelamin', 'jalur']));
    }
};
