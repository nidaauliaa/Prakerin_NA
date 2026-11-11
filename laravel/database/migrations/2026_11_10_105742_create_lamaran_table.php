<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamaran', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('kode', 20)->unique();           // contoh: LMR-2026-0001 (untuk cek status pendaftar)

            $table->foreignId('lowongan_id')
                ->constrained('lowongan')
                ->restrictOnDelete();

            // Snapshot jenjang saat daftar (nama kolom sama dengan data_magang)
            $table->enum('tipe_magang', ['smk', 'kuliah']);

            // Data wajib semua jenjang
            $table->string('nama', 150);
            $table->string('email', 150);
            $table->string('no_wa', 20);
            $table->text('alamat');
            $table->string('foto_pas', 255);
            $table->string('asal_sekolah', 150);            // nama sekolah / kampus
            $table->string('jurusan', 150);

            // Opsional di database; wajib/tidaknya diatur di validasi (lihat catatan)
            //  - SMK    : pembimbing + no_wa_pembimbing WAJIB
            //  - Kuliah : dosen pembimbing + no_wa_pembimbing OPSIONAL
            $table->string('pembimbing', 150)->nullable();
            $table->string('no_wa_pembimbing', 20)->nullable();
            $table->string('cv_path', 255)->nullable();
            $table->string('surat_permohonan_path', 255)->nullable();

            // Dibutuhkan admin saat menerima (jadi username login absensi); tidak ada di form publik
            $table->string('nis_nim', 50)->nullable();

            // Periode magang yang diajukan
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');

            // Keputusan admin
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diputuskan_oleh')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->timestamp('diputuskan_pada')->nullable();

            // Terisi setelah pelamar diterima dan dipindahkan ke sistem absensi
            $table->foreignId('data_magang_id')
                ->nullable()
                ->unique()
                ->constrained('data_magang')
                ->nullOnDelete();

            $table->timestamps();

            // Hitung keterisian kuota per rentang tanggal
            $table->index(['lowongan_id', 'status', 'tgl_mulai', 'tgl_selesai'], 'lamaran_okupansi_index');
            $table->index('email');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lamaran');
    }
};