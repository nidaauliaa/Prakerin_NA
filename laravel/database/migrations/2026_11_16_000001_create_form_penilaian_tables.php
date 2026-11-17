<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form penilaian (dibuat admin, dibagikan lewat link) + jawaban penilai.
     *  - form_penilaian          : satu form = satu link (kolom `kode`)
     *  - form_penilaian_kriteria : butir yang dinilai (0-100) beserta bobotnya
     *  - penilaian               : satu isian penilai untuk satu peserta
     *  - penilaian_nilai         : nilai per butir (nama & bobot disalin supaya aman bila butir form diubah)
     */
    public function up(): void
    {
        Schema::create('form_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();            // bagian akhir link publik
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->foreignId('departemen_id')->nullable()->constrained('departemen')->nullOnDelete(); // batasi daftar peserta
            $table->enum('status', ['dibuka', 'ditutup'])->default('dibuka');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('form_penilaian_kriteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_penilaian_id')->constrained('form_penilaian')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('keterangan', 255)->nullable();
            $table->unsignedSmallInteger('bobot')->default(1);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_penilaian_id')->constrained('form_penilaian')->cascadeOnDelete();
            $table->foreignId('data_magang_id')->constrained('data_magang')->cascadeOnDelete();
            $table->string('nama_penilai', 150);
            $table->string('jabatan_penilai', 150);
            $table->decimal('nilai_akhir', 5, 2);            // rata-rata berbobot, 0 - 100
            $table->string('predikat', 30);
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Satu peserta hanya dinilai satu kali per form.
            $table->unique(['form_penilaian_id', 'data_magang_id']);
        });

        Schema::create('penilaian_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian')->cascadeOnDelete();
            $table->foreignId('kriteria_id')->nullable()->constrained('form_penilaian_kriteria')->nullOnDelete();
            $table->string('nama_kriteria', 150);
            $table->unsignedSmallInteger('bobot');
            $table->unsignedTinyInteger('nilai');            // 0 - 100
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_nilai');
        Schema::dropIfExists('penilaian');
        Schema::dropIfExists('form_penilaian_kriteria');
        Schema::dropIfExists('form_penilaian');
    }
};
