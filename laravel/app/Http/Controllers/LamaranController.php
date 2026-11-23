<?php

namespace App\Http\Controllers;

use App\Http\Request\PilihPeriodeRequest;
use App\Http\Request\StoreLamaranRequest;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Pelamar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LamaranController extends Controller
{
    /** Langkah 1: simpan pilihan posisi + periode di session, lalu arahkan ke login / form data. */
    public function pilih(PilihPeriodeRequest $request, Lowongan $lowongan)
    {
        if ($batas = $this->tolakBilaBatasTercapai()) {
            return $batas;
        }

        $mulai   = Carbon::parse($request->tgl_mulai)->startOfDay();
        $selesai = Carbon::parse($request->tgl_selesai)->startOfDay();

        $this->pastikanTersedia($lowongan, $mulai, $selesai, route('beranda') . '#lowongan');

        // Profil sudah terisi dari lamaran pertama: tidak perlu isi data diri lagi.
        // Lamaran langsung terkirim begitu persyaratan umum disetujui (popup di beranda).
        $pelamar = Auth::guard('pelamar')->user();
        if ($pelamar && $pelamar->profil()) {
            if (! $request->boolean('setuju_persyaratan')) {
                return redirect(route('beranda') . '#lowongan')
                    ->with('info', 'Setujui persyaratan umum terlebih dahulu untuk mengirim lamaran.');
            }

            return $this->lamarDenganProfil($pelamar, $lowongan, $mulai, $selesai);
        }

        $request->session()->put('lamaran_draft', [
            'lowongan_id' => $lowongan->id,
            'tgl_mulai'   => $mulai->toDateString(),
            'tgl_selesai' => $selesai->toDateString(),
        ]);

        if (Auth::guard('pelamar')->check()) {
            return redirect()->route('lamaran.form');
        }

        return redirect()->route('pelamar.login')
            ->with('info', 'Silakan masuk atau daftar untuk melanjutkan lamaran Anda.');
    }

    /** Langkah 2: halaman lengkapi data (butuh login pelamar). */
    public function form()
    {
        if ($batas = $this->tolakBilaBatasTercapai()) {
            return $batas;
        }

        $draft    = session('lamaran_draft');
        $lowongan = $draft ? Lowongan::dibuka()->find($draft['lowongan_id']) : null;

        if (! $lowongan) {
            return redirect(route('beranda') . '#lowongan')
                ->with('info', 'Silakan pilih posisi dan periode magang terlebih dahulu.');
        }

        $pelamar = Auth::guard('pelamar')->user();
        $data    = [
            'lowongan' => $lowongan,
            'mulai'    => Carbon::parse($draft['tgl_mulai']),
            'selesai'  => Carbon::parse($draft['tgl_selesai']),
            'pelamar'  => $pelamar,
        ];

        // Profil sudah ada (mis. login dulu baru lanjut): cukup konfirmasi + setujui persyaratan umum.
        if ($profil = $pelamar->profil()) {
            return view('lamaran.konfirmasi', $data + ['profil' => $profil]);
        }

        return view('lamaran.form', $data);
    }

    /** Lamaran berikutnya (profil sudah ada): hanya perlu persetujuan persyaratan umum. */
    public function konfirmasi(Request $request)
    {
        if ($batas = $this->tolakBilaBatasTercapai()) {
            return $batas;
        }

        $request->validate(
            ['setuju_persyaratan' => ['accepted']],
            ['setuju_persyaratan.accepted' => 'Anda harus menyetujui persyaratan umum untuk mengirim lamaran.']
        );

        $draft    = $request->session()->get('lamaran_draft');
        $lowongan = $draft ? Lowongan::dibuka()->find($draft['lowongan_id']) : null;

        if (! $lowongan) {
            return redirect(route('beranda') . '#lowongan')
                ->with('info', 'Pilihan periode Anda sudah berakhir. Silakan pilih posisi dan periode lagi.');
        }

        return $this->lamarDenganProfil(
            Auth::guard('pelamar')->user(),
            $lowongan,
            Carbon::parse($draft['tgl_mulai']),
            Carbon::parse($draft['tgl_selesai'])
        );
    }

    /** Langkah 3: simpan lamaran. */
    public function store(StoreLamaranRequest $request)
    {
        if ($batas = $this->tolakBilaBatasTercapai()) {
            return $batas;
        }

        $pelamar  = Auth::guard('pelamar')->user();
        $draft    = $request->draft();
        $lowongan = $request->lowongan();
        $mulai    = Carbon::parse($draft['tgl_mulai']);
        $selesai  = Carbon::parse($draft['tgl_selesai']);

        // Kuota bisa berubah sejak pelamar memilih periode: cek ulang.
        $this->pastikanTersedia($lowongan, $mulai, $selesai, route('beranda') . '#lowongan');

        $sudahAda = Lamaran::where('lowongan_id', $lowongan->id)
            ->where('pelamar_id', $pelamar->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'nama' => 'Anda masih punya lamaran untuk posisi ini yang menunggu diproses.',
            ])->redirectTo(route('lamaran.form'));
        }

        // Berkas disimpan di disk PRIVAT (storage/app/private).
        $lamaran = Lamaran::create([
            'lowongan_id'           => $lowongan->id,
            'pelamar_id'            => $pelamar->id,
            'tipe_magang'           => $lowongan->tipe_magang,
            'nama'                  => $request->nama,
            'jenis_kelamin'         => $request->jenis_kelamin,
            'nis_nim'               => $request->nis_nim,
            'email'                 => $pelamar->email,
            'no_wa'                 => $request->no_wa,
            'alamat'                => $request->alamat,
            'foto_pas'              => $request->file('foto_pas')->store('lamaran/foto'),
            'asal_sekolah'          => $request->asal_sekolah,
            'jurusan'               => $request->jurusan,
            'pembimbing'            => $request->pembimbing,
            'no_wa_pembimbing'      => $request->no_wa_pembimbing,
            'cv_path'               => $request->file('cv')?->store('lamaran/cv'),
            'surat_permohonan_path' => $request->file('surat_permohonan')?->store('lamaran/surat'),
            'tgl_mulai'             => $mulai,
            'tgl_selesai'           => $selesai,
            'status'                => 'menunggu',
        ]);

        // Data diri dari lamaran pertama sekaligus menjadi profil akun (tak perlu diisi lagi).
        $pelamar->update([
            'tipe_magang'           => $lowongan->tipe_magang,
            'nama'                  => $lamaran->nama,
            'jenis_kelamin'         => $lamaran->jenis_kelamin,
            'nis_nim'               => $lamaran->nis_nim,
            'no_wa'                 => $lamaran->no_wa,
            'alamat'                => $lamaran->alamat,
            'asal_sekolah'          => $lamaran->asal_sekolah,
            'jurusan'               => $lamaran->jurusan,
            'pembimbing'            => $lamaran->pembimbing,
            'no_wa_pembimbing'      => $lamaran->no_wa_pembimbing,
            'foto_pas'              => $lamaran->foto_pas,
            'cv_path'               => $lamaran->cv_path,
            'surat_permohonan_path' => $lamaran->surat_permohonan_path,
        ]);

        $request->session()->forget('lamaran_draft');

        return redirect(route('beranda') . '#lowongan')->with('lamaran_berhasil', $this->infoBerhasil($lamaran, $lowongan, $mulai, $selesai));
    }

    /** Isi notifikasi "Lamaran berhasil dikirim" (dibaca di beranda.blade.php). */
    private function infoBerhasil(Lamaran $lamaran, Lowongan $lowongan, Carbon $mulai, Carbon $selesai): array
    {
        $fmt = fn (Carbon $d) => $d->copy()->locale('id')->translatedFormat('j F Y');

        return [
            'kode'    => $lamaran->kode,
            'posisi'  => $lowongan->judul,
            'mulai'   => $fmt($mulai),
            'selesai' => $fmt($selesai),
        ];
    }

    /** Membuat lamaran baru dengan menyalin data diri & berkas dari lamaran pertama (profil). */
    private function lamarDenganProfil(Pelamar $pelamar, Lowongan $lowongan, Carbon $mulai, Carbon $selesai)
    {
        $profil = $pelamar->profil();
        $label  = fn (string $t) => $t === 'smk' ? 'Anak SMK' : 'Mahasiswa';
        $kembali = route('beranda') . '#lowongan';

        // Profil mengikuti jenjang lamaran pertama (SMK / Mahasiswa): syarat berkas keduanya berbeda.
        if ($profil->tipe_magang !== $lowongan->tipe_magang) {
            session()->forget('lamaran_draft');

            return redirect($kembali)->with('info', sprintf(
                'Profil Anda terdaftar sebagai %s, jadi hanya bisa melamar posisi untuk jenjang yang sama. Posisi ini untuk %s.',
                $label($profil->tipe_magang),
                $label($lowongan->tipe_magang)
            ));
        }

        $this->pastikanTersedia($lowongan, $mulai, $selesai, $kembali);

        $sudahAda = Lamaran::where('lowongan_id', $lowongan->id)
            ->where('pelamar_id', $pelamar->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($sudahAda) {
            session()->forget('lamaran_draft');

            return redirect($kembali)->with('info', 'Anda masih punya lamaran untuk posisi ini yang menunggu diproses.');
        }

        $lamaran = Lamaran::create([
            'lowongan_id'           => $lowongan->id,
            'pelamar_id'            => $pelamar->id,
            'tipe_magang'           => $lowongan->tipe_magang,
            'nama'                  => $profil->nama,
            'jenis_kelamin'         => $profil->jenis_kelamin,
            'nis_nim'               => $profil->nis_nim,
            'email'                 => $pelamar->email,
            'no_wa'                 => $profil->no_wa,
            'alamat'                => $profil->alamat,
            'foto_pas'              => $profil->foto_pas,
            'asal_sekolah'          => $profil->asal_sekolah,
            'jurusan'               => $profil->jurusan,
            'pembimbing'            => $profil->pembimbing,
            'no_wa_pembimbing'      => $profil->no_wa_pembimbing,
            'cv_path'               => $profil->cv_path,
            'surat_permohonan_path' => $profil->surat_permohonan_path,
            'tgl_mulai'             => $mulai,
            'tgl_selesai'           => $selesai,
            'status'                => 'menunggu',
        ]);

        session()->forget('lamaran_draft');

        return redirect($kembali)->with('lamaran_berhasil', $this->infoBerhasil($lamaran, $lowongan, $mulai, $selesai));
    }

    /** Satu pelamar maksimal Pelamar::MAKS_LAMARAN lamaran (semua status dihitung = riwayat). */
    private function tolakBilaBatasTercapai()
    {
        $pelamar = Auth::guard('pelamar')->user();

        // Satu pelamar hanya bisa diterima di satu posisi.
        if ($pelamar && $pelamar->sudahDiterima()) {
            session()->forget('lamaran_draft');

            return redirect(route('beranda') . '#lowongan')->with(
                'info',
                'Anda sudah diterima di salah satu posisi, jadi tidak bisa melamar posisi lain. Lihat detailnya di Dashboard Peserta.'
            );
        }

        if ($pelamar && $pelamar->sisaLamaran() < 1) {
            session()->forget('lamaran_draft');

            return redirect(route('beranda') . '#lowongan')->with(
                'info',
                'Anda sudah mengajukan ' . Pelamar::MAKS_LAMARAN . ' lamaran (batas maksimal per pelamar). Pantau statusnya di Dashboard Peserta.'
            );
        }

        return null;
    }

    private function pastikanTersedia(Lowongan $lowongan, Carbon $mulai, Carbon $selesai, string $redirect): void
    {
        if ($penuh = $lowongan->cekKetersediaan($mulai, $selesai)) {
            throw ValidationException::withMessages([
                'tgl_mulai' => sprintf(
                    'Periode %s s/d %s sudah penuh (%d/%d orang). Silakan geser tanggal Anda.',
                    $penuh['dari']->translatedFormat('j M Y'),
                    $penuh['sampai']->translatedFormat('j M Y'),
                    $lowongan->kuota,
                    $lowongan->kuota
                ),
            ])->redirectTo($redirect);
        }
    }
}