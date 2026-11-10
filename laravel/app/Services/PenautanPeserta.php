<?php

namespace App\Services;

use App\Models\DataMagang;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Menautkan AKUN peserta (yang daftar sendiri lewat web) ke baris data magang dari Excel, lewat NIM/NISN.
// Setelah ditautkan: akun mendapat nomor induk, divisi, asal sekolah, jurusan, dan UUID kartu (uuid_kartu),
// sehingga peserta bisa scan presensi. Dipanggil dari Artisan (prakerin:tautkan) maupun dari halaman admin:
//     try { app(PenautanPeserta::class)->jalankan($user, $nisNim); }
//     catch (\DomainException $e) { /* $e->getMessage() = pesan Indonesia yang siap ditampilkan */ }
class PenautanPeserta
{
    public function jalankan(User $user, string $nisNim, bool $timpaNama = false): DataMagang
    {
        $nisNim = trim($nisNim);

        return DB::transaction(function () use ($user, $nisNim, $timpaNama) {
            $akun = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $data = DataMagang::where('nis_nim', $nisNim)->lockForUpdate()->first();

            if ($akun->peran !== 'peserta') {
                throw new \DomainException('Hanya akun berperan peserta yang bisa ditautkan.');
            }
            if (! $data) {
                throw new \DomainException("NIM/NISN {$nisNim} tidak ditemukan di data magang. Impor file Excel-nya dulu.");
            }
            if ($data->user_id !== null && (int) $data->user_id !== (int) $akun->id) {
                throw new \DomainException("NIM/NISN {$nisNim} sudah ditautkan ke akun lain.");
            }
            if ($akun->nis_nim !== null && $akun->nis_nim !== $nisNim) {
                throw new \DomainException("Akun ini sudah ditautkan ke NIM/NISN {$akun->nis_nim}.");
            }
            $dipakaiAkunLain = User::where('id', '<>', $akun->id)
                ->where(fn ($q) => $q->where('nis_nim', $nisNim)->orWhere('uuid_kartu', $data->uuid))
                ->exists();
            if ($dipakaiAkunLain) {
                throw new \DomainException('Nomor induk atau UUID kartu ini sudah dipakai akun lain.');
            }

            $akun->fill([
                'nis_nim' => $data->nis_nim,
                'uuid_kartu' => $data->uuid,
                'departemen_id' => $data->departemen_id,
                'asal_sekolah' => $data->asal_sekolah,
                'jurusan' => $data->jurusan,
                'status_akun' => config('prakerin.status_setelah_tautan'),
            ]);
            if ($timpaNama) {
                $akun->name = $data->nama; // nama resmi dari Excel menggantikan nama yang diketik saat daftar
            }
            $akun->save();

            $data->update(['user_id' => $akun->id, 'ditautkan_pada' => now()]);

            return $data;
        });
    }
}
