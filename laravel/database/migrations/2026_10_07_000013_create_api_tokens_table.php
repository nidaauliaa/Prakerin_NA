<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Token akses API untuk divisi IT. Yang disimpan hanya HASH token (SHA-256),
// jadi token asli tidak bisa dibaca ulang dari database. Token asli hanya tampil
// sekali, saat dibuat di halaman admin.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('api_tokens')) {
            return;
        }

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);                 // pemilik token, mis. "Budi - Divisi IT"
            $table->string('token_hash', 64)->unique();  // sha256 dari token asli
            $table->string('token_awal', 8);             // 8 karakter pertama, untuk mengenali token di daftar
            $table->foreignId('dibuat_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('berlaku_sampai')->nullable();   // NULL = tidak kedaluwarsa
            $table->timestamp('terakhir_dipakai')->nullable();
            $table->timestamp('dicabut_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
