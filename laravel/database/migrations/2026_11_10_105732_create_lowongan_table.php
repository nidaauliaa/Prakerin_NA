<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongan', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('judul', 150);

            // Skema lowongan: smk / kuliah (sama dengan data_magang.tipe_magang)
            $table->enum('tipe_magang', ['smk', 'kuliah']);

            // Opsional: kaitkan ke departemen yang sudah ada di sistem absensi
            $table->foreignId('departemen_id')
                ->nullable()
                ->constrained('departemen')
                ->nullOnDelete();

            $table->text('deskripsi')->nullable();
            $table->text('jurusan_diterima')->nullable();   // contoh: "SMK Teknik Mesin / Otomotif"

            // Kuota = maksimal peserta yang boleh magang BERSAMAAN di posisi ini pada satu hari
            $table->unsignedSmallInteger('kuota')->default(1);
            $table->string('lokasi', 100)->default('Magelang');

            $table->enum('status', ['draft', 'dibuka', 'ditutup'])->default('draft');
            $table->date('batas_pendaftaran')->nullable();

            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tipe_magang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongan');
    }
};