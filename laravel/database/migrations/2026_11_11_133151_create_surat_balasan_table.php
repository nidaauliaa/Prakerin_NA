<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Surat balasan ijin prakerin: diisi HRD setelah menerima lamaran, diunduh pelamar sebagai PDF. */
    public function up(): void
    {
        Schema::create('surat_balasan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lamaran_id')->unique()->constrained('lamaran')->cascadeOnDelete();

            $table->string('nomor_surat', 30);              // bagian depan saja, mis. "045"
            $table->date('tanggal_surat');
            $table->string('ditujukan_kepada', 150);        // "Bapak/Ibu ...."
            $table->date('tgl_pengajuan');                  // tanggal surat permohonan dari kampus/sekolah
            $table->string('media_pengajuan', 100);         // mis. "Elektronik (Email)"
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->date('tgl_induction');

            $table->foreignId('dibuat_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_balasan');
    }
};
