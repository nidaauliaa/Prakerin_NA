<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil peserta disimpan di akun pelamar (bukan lagi hanya di lamaran pertama), supaya peserta
     * bisa melengkapi profil sendiri lebih dulu -- termasuk peserta jalur internal yang dicari HRD
     * lewat nama / NIS-NIM.
     */
    public function up(): void
    {
        Schema::table('pelamar', function (Blueprint $table) {
            $table->string('tipe_magang', 10)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('nis_nim', 50)->nullable()->index();
            $table->string('no_wa', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('asal_sekolah', 150)->nullable();
            $table->string('jurusan', 150)->nullable();
            $table->string('pembimbing', 150)->nullable();
            $table->string('no_wa_pembimbing', 20)->nullable();
            $table->string('foto_pas', 255)->nullable();
            $table->string('cv_path', 255)->nullable();
            $table->string('surat_permohonan_path', 255)->nullable();
        });

        // Pelamar lama: profilnya diambil dari lamaran pertama mereka.
        DB::table('lamaran')->whereNotNull('pelamar_id')->orderBy('id')->get()->groupBy('pelamar_id')
            ->each(function ($baris, $pelamarId) {
                $l = $baris->first();
                DB::table('pelamar')->where('id', $pelamarId)->update([
                    'tipe_magang'           => $l->tipe_magang,
                    'jenis_kelamin'         => $l->jenis_kelamin,
                    'nis_nim'               => $l->nis_nim,
                    'no_wa'                 => $l->no_wa,
                    'alamat'                => $l->alamat,
                    'asal_sekolah'          => $l->asal_sekolah,
                    'jurusan'               => $l->jurusan,
                    'pembimbing'            => $l->pembimbing,
                    'no_wa_pembimbing'      => $l->no_wa_pembimbing,
                    'foto_pas'              => $l->foto_pas,
                    'cv_path'               => $l->cv_path,
                    'surat_permohonan_path' => $l->surat_permohonan_path,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('pelamar', fn (Blueprint $table) => $table->dropIndex(['nis_nim']));

        Schema::table('pelamar', function (Blueprint $table) {
            $table->dropColumn(['tipe_magang', 'jenis_kelamin', 'nis_nim', 'no_wa', 'alamat', 'asal_sekolah', 'jurusan',
                'pembimbing', 'no_wa_pembimbing', 'foto_pas', 'cv_path', 'surat_permohonan_path']);
        });
    }
};
