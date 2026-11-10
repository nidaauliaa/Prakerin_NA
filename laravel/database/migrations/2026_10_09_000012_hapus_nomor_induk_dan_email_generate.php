<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// - Hapus kolom nomor_induk (MN3-2026-xxx, hasil generate) dari users dan data_magang.
//   NIS/NIM asli sekarang ada di kolom nis_nim (users & data_magang), dan itu yang dipakai login.
// - Kosongkan email hasil generate (@legacy.local / @peserta.local). Kolom email jadi boleh NULL.
return new class extends Migration
{
    public function up(): void
    {
        // 1. users.nis_nim (kalau migration sebelumnya belum menambahkannya)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nis_nim')) {
                $table->string('nis_nim', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'wajib_ganti_password')) {
                $table->boolean('wajib_ganti_password')->default(false)->after('password');
            }
        });

        // 2. data_magang: nis_nim kosong -> NULL. Data hasil impor lama menyimpan NIM asli di nomor_induk.
        DB::table('data_magang')->where('nis_nim', '')->update(['nis_nim' => null]);
        if (Schema::hasColumn('data_magang', 'nomor_induk')) {
            DB::table('data_magang')
                ->whereNull('nis_nim')
                ->where('nomor_induk', 'not like', 'MN3-%')
                ->update(['nis_nim' => DB::raw('nomor_induk')]);
        }

        // 3. users.nis_nim diisi dari data_magang.nis_nim (NIM kembar dilewati).
        $dipakai = DB::table('users')->whereNotNull('nis_nim')->pluck('nis_nim')->flip()->all();
        $baris = DB::table('data_magang')->whereNotNull('user_id')->whereNotNull('nis_nim')
            ->get(['user_id', 'nis_nim']);
        foreach ($baris as $b) {
            $nim = trim($b->nis_nim);
            if ($nim === '' || isset($dipakai[$nim])) {
                continue;
            }
            $dipakai[$nim] = true;
            DB::table('users')->where('id', $b->user_id)->whereNull('nis_nim')->update(['nis_nim' => $nim]);
        }

        // 4. Index unik nis_nim (data_magang harus tanpa NIM kembar)
        $kembar = DB::table('data_magang')->whereNotNull('nis_nim')->groupBy('nis_nim')
            ->havingRaw('COUNT(*) > 1')->pluck('nis_nim');
        if ($kembar->isNotEmpty()) {
            throw new RuntimeException('NIS/NIM kembar di data_magang: ' . $kembar->implode(', ') . '. Perbaiki dulu, lalu jalankan migrate lagi.');
        }
        if (!Schema::hasIndex('users', 'users_nis_nim_unique')) {
            Schema::table('users', fn (Blueprint $t) => $t->unique('nis_nim'));
        }
        if (!Schema::hasIndex('data_magang', 'data_magang_nis_nim_unique')) {
            Schema::table('data_magang', fn (Blueprint $t) => $t->unique('nis_nim'));
        }

        // 5. Hapus kolom nomor_induk
        if (Schema::hasColumn('data_magang', 'nomor_induk')) {
            Schema::table('data_magang', function (Blueprint $table) {
                if (Schema::hasIndex('data_magang', 'data_magang_nomor_induk_unique')) {
                    $table->dropUnique('data_magang_nomor_induk_unique');
                }
                $table->dropColumn('nomor_induk');
            });
        }
        if (Schema::hasColumn('users', 'nomor_induk')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasIndex('users', 'users_nomor_induk_unique')) {
                    $table->dropUnique('users_nomor_induk_unique');
                }
                $table->dropColumn('nomor_induk');
            });
        }

        // 6. Email boleh kosong, lalu kosongkan email hasil generate
        Schema::table('users', fn (Blueprint $t) => $t->string('email')->nullable()->change());
        DB::table('users')
            ->where(fn ($q) => $q->where('email', 'like', '%@legacy.local')->orWhere('email', 'like', '%@peserta.local'))
            ->update(['email' => null]);
    }

    public function down(): void
    {
        // Tidak bisa dikembalikan penuh (isi nomor_induk dan email generate sudah hilang). Hanya kolomnya dibuat lagi.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nomor_induk')) {
                $table->string('nomor_induk')->nullable()->unique()->after('email');
            }
        });
        Schema::table('data_magang', function (Blueprint $table) {
            if (!Schema::hasColumn('data_magang', 'nomor_induk')) {
                $table->string('nomor_induk', 50)->nullable()->unique()->after('id');
            }
        });
    }
};
