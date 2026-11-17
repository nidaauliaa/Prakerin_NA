<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Surat Keterangan selesai prakerin: dibuat otomatis (draft) saat peserta dinilai, diterbitkan HRD. */
    public function up(): void
    {
        Schema::create('surat_keterangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_magang_id')->unique()->constrained('data_magang')->cascadeOnDelete();

            $table->string('nomor_surat', 30)->nullable();   // bagian depan saja, mis. "045"
            $table->date('tanggal_surat')->nullable();
            $table->string('penempatan', 150)->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();

            $table->decimal('nilai_akhir', 5, 2)->nullable(); // rata-rata semua penilaian peserta
            $table->string('predikat', 30)->nullable();
            $table->unsignedSmallInteger('jumlah_penilaian')->default(0);

            $table->enum('status', ['draft', 'terbit'])->default('draft');
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan');
    }
};
