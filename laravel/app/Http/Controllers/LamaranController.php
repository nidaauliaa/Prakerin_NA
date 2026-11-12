<?php

namespace App\Http\Controllers;

use App\Http\Request\PilihPeriodeRequest;
use App\Http\Request\StoreLamaranRequest;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Pelamar;
use Carbon\Carbon;
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

        return view('lamaran.form', [
            'lowongan' => $lowongan,
            'mulai'    => Carbon::parse($draft['tgl_mulai']),
            'selesai'  => Carbon::parse($draft['tgl_selesai']),
            'pelamar'  => Auth::guard('pelamar')->user(),
        ]);
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

        $request->session()->forget('lamaran_draft');

        return redirect(route('beranda') . '#lowongan')->with('lamaran_berhasil', [
            'kode'   => $lamaran->kode,
            'posisi' => $lowongan->judul,
        ]);
    }

    /** Satu pelamar maksimal Pelamar::MAKS_LAMARAN lamaran (semua status dihitung = riwayat). */
    private function tolakBilaBatasTercapai()
    {
        $pelamar = Auth::guard('pelamar')->user();

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