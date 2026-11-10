<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kalender Kerja: hari libur (satu hari atau rentang) + hari kerja pengganti, diatur admin.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hari_libur')) {
            Schema::create('hari_libur', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->enum('jenis', ['cuti_bersama', 'libur_nasional', 'lainnya'])->default('libur_nasional');
                $table->string('jenis_label', 60)->nullable(); // diisi bila jenis = lainnya ("tulis sendiri")
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');               // sama dengan tanggal_mulai bila libur 1 hari
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['tanggal_mulai', 'tanggal_selesai']);
            });
        }

        if (! Schema::hasTable('hari_pengganti')) {
            Schema::create('hari_pengganti', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hari_libur_id')->constrained('hari_libur')->cascadeOnDelete();
                $table->date('tanggal')->unique(); // tanggal yang biasanya libur (mis. Sabtu) tapi jadi hari kerja
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_pengganti');
        Schema::dropIfExists('hari_libur');
    }
};
