<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// nis_nim = NIS/NIM asli peserta (dipakai untuk login).
// Kolom nomor_induk (MN3-2026-xxx) dihapus oleh migration 2026_10_09_000012.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nis_nim')) {
                $table->string('nis_nim', 50)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'wajib_ganti_password')) {
                $table->boolean('wajib_ganti_password')->default(false)->after('password');
            }
        });

        // Isi nis_nim dari data_magang.nis_nim. NIM kembar/kosong dilewati (nis_nim tetap NULL).
        $baris = DB::table('data_magang')
            ->whereNotNull('user_id')->whereNotNull('nis_nim')->where('nis_nim', '!=', '')
            ->get(['user_id', 'nis_nim']);

        $dipakai = [];
        foreach ($baris as $b) {
            $nim = trim($b->nis_nim);
            if ($nim === '' || isset($dipakai[$nim])) {
                continue;
            }
            $dipakai[$nim] = true;
            DB::table('users')->where('id', $b->user_id)->whereNull('nis_nim')->update(['nis_nim' => $nim]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nis_nim')) {
                $table->dropUnique(['nis_nim']);
                $table->dropColumn('nis_nim');
            }
            if (Schema::hasColumn('users', 'wajib_ganti_password')) {
                $table->dropColumn('wajib_ganti_password');
            }
        });
    }
};
