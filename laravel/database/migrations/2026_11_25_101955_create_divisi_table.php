<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi/Section di bawah Departemen. Lowongan dibuka per DIVISI/SECTION (bukan per departemen);
     * peserta jalur internal juga ditempatkan di suatu divisi/section.
     */
    public function up(): void
    {
        Schema::create('divisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departemen_id')->constrained('departemen')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->text('jurusan')->nullable();      // kualifikasi jurusan (dari daftar Excel)
            $table->timestamps();

            $table->unique(['departemen_id', 'nama']);
        });

        Schema::table('lowongan', function (Blueprint $table) {
            $table->foreignId('divisi_id')->nullable()->after('departemen_id')->constrained('divisi')->nullOnDelete();
        });

        // Dipakai peserta jalur internal (tanpa lowongan) untuk mencatat posisi/section penempatannya.
        Schema::table('lamaran', function (Blueprint $table) {
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lamaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('divisi_id');
        });
        Schema::table('lowongan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('divisi_id');
        });
        Schema::dropIfExists('divisi');
    }
};
