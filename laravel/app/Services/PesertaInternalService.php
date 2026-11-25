<?php

namespace App\Services;

use App\Models\DataMagang;
use App\Models\Divisi;
use App\Models\Lamaran;
use App\Models\Pelamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Jalur INTERNAL: HRD memasukkan peserta yang SUDAH punya akun portal & profil (dicari lewat nama / NIS-NIM).
 * HRD hanya memilih posisi (section) dan periode (tanpa batas durasi). Dalam satu transaksi dibuat:
 *  - akun presensi (users) + data_magang, memakai kata sandi akun portal peserta,
 *  - lamaran jalur internal berstatus "diterima" (muncul di riwayat & dashboard peserta, bisa dibuatkan surat balasan).
 */
class PesertaInternalService
{
    public function buat(Pelamar $pelamar, int $divisiId, string $mulai, string $selesai, ?int $adminId): array
    {
        $this->pastikanBisaDimasukkan($pelamar);

        $divisi     = Divisi::with('departemen')->findOrFail($divisiId);
        $departemen = $divisi->departemen;
        $publik     = Storage::disk('public');
        $privat     = Storage::disk('local');

        // data_magang memakai berkas di disk public; berkas pelamar ada di disk privat -> disalin.
        $salin = function (?string $path, string $folder) use ($privat, $publik) {
            if (! $path || ! $privat->exists($path)) {
                return null;
            }
            $baru = $folder . '/' . Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            $publik->put($baru, $privat->get($path));

            return $baru;
        };

        $foto  = $salin($pelamar->foto_pas, 'peserta/foto');
        $cv    = $salin($pelamar->cv_path, 'peserta/cv');
        $surat = $salin($pelamar->surat_permohonan_path, 'peserta/surat');

        try {
            return DB::transaction(function () use ($pelamar, $divisi, $departemen, $mulai, $selesai, $adminId, $foto, $cv, $surat, $publik) {
                $uuid = (string) Str::uuid();
                $tglMulai   = Carbon::parse($mulai);
                $tglSelesai = Carbon::parse($selesai);

                $user = User::create([
                    'name'                 => $pelamar->nama,
                    'email'                => mb_strtolower($pelamar->email),
                    'nis_nim'              => $pelamar->nis_nim,
                    // Satu sandi untuk portal & presensi: pakai sandi pilihan peserta (sudah berupa hash).
                    'password'             => $pelamar->getRawOriginal('password'),
                    'wajib_ganti_password' => false,
                    'peran'                => 'peserta',
                    'status_akun'          => 'aktif',
                    'departemen_id'        => $departemen->id,
                    'uuid_kartu'           => $uuid,
                    'foto_profil_url'      => $foto ? $publik->url($foto) : null,
                    'asal_sekolah'         => $pelamar->asal_sekolah,
                    'jurusan'              => $pelamar->jurusan,
                ]);

                $dm = DataMagang::create([
                    'nama'                  => $pelamar->nama,
                    'tipe_magang'           => $pelamar->tipe_magang,
                    'jenis_kelamin'         => $pelamar->jenis_kelamin,
                    'no_wa'                 => $pelamar->no_wa,
                    'alamat'                => $pelamar->alamat,
                    'nis_nim'               => $pelamar->nis_nim,
                    'asal_sekolah'          => $pelamar->asal_sekolah,
                    'jurusan'               => $pelamar->jurusan,
                    'departemen_id'         => $departemen->id,
                    'divisi'                => $divisi->nama,
                    'uuid'                  => $uuid,
                    'user_id'               => $user->id,
                    'ditautkan_pada'        => now(),
                    'foto_pas'              => $foto,
                    'pembimbing'            => $pelamar->pembimbing,
                    'no_wa_pembimbing'      => $pelamar->no_wa_pembimbing,
                    'cv_path'               => $cv,
                    'surat_permohonan_path' => $surat,
                    // Format sama dengan data import: "04/05/2026 - 04/11/2026"
                    'periode_magang'        => $tglMulai->format('d/m/Y') . ' - ' . $tglSelesai->format('d/m/Y'),
                    'status_kehadiran_awal' => 'hadir',
                    'tgl_mulai'             => $tglMulai->toDateString(),
                    'tgl_selesai'           => $tglSelesai->toDateString(),
                    'status_magang'         => 'ACTIVE',
                ]);

                $lamaran = Lamaran::create([
                    'jalur'                 => 'internal',
                    'lowongan_id'           => null,
                    'divisi_id'             => $divisi->id,
                    'pelamar_id'            => $pelamar->id,
                    'tipe_magang'           => $pelamar->tipe_magang,
                    'nama'                  => $pelamar->nama,
                    'jenis_kelamin'         => $pelamar->jenis_kelamin,
                    'email'                 => $pelamar->email,
                    'no_wa'                 => $pelamar->no_wa,
                    'alamat'                => $pelamar->alamat,
                    'foto_pas'              => $pelamar->foto_pas,
                    'asal_sekolah'          => $pelamar->asal_sekolah,
                    'jurusan'               => $pelamar->jurusan,
                    'pembimbing'            => $pelamar->pembimbing,
                    'no_wa_pembimbing'      => $pelamar->no_wa_pembimbing,
                    'cv_path'               => $pelamar->cv_path,
                    'surat_permohonan_path' => $pelamar->surat_permohonan_path,
                    'nis_nim'               => $pelamar->nis_nim,
                    'tgl_mulai'             => $tglMulai,
                    'tgl_selesai'           => $tglSelesai,
                    'status'                => 'diterima',
                    'diputuskan_oleh'       => $adminId,
                    'diputuskan_pada'       => now(),
                    'data_magang_id'        => $dm->id,
                ]);

                // Hanya bisa diterima di 1 posisi: lamaran portal lain yang masih menunggu ditolak otomatis.
                $otomatis = Lamaran::tolakLainnyaOtomatis($lamaran, $adminId);

                return compact('user', 'lamaran', 'otomatis');
            });
        } catch (\Throwable $e) {
            $publik->delete(array_filter([$foto, $cv, $surat]));   // jangan tinggalkan berkas yatim
            throw $e;
        }
    }

    /** @throws ValidationException */
    public function pastikanBisaDimasukkan(Pelamar $pelamar): void
    {
        $galat = null;

        if (! $pelamar->profilLengkap()) {
            $galat = "{$pelamar->nama} belum melengkapi profil. Minta peserta mengisi profil di Dashboard Peserta terlebih dahulu.";
        } elseif ($pelamar->sudahDiterima()) {
            $galat = "{$pelamar->nama} sudah diterima sebagai peserta (satu pelamar hanya bisa diterima di satu posisi).";
        } elseif (User::where('nis_nim', $pelamar->nis_nim)->exists() || DataMagang::where('nis_nim', $pelamar->nis_nim)->exists()) {
            $galat = "NIS/NIM {$pelamar->nis_nim} sudah terdaftar sebagai peserta lain.";
        } elseif (User::where('email', mb_strtolower($pelamar->email))->exists()) {
            $galat = "Email {$pelamar->email} sudah dipakai akun presensi lain.";
        }

        if ($galat) {
            throw ValidationException::withMessages(['pelamar' => $galat]);
        }
    }
}
