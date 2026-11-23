<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\SuratKeterangan;
use App\Services\SuratBalasanPdf;
use App\Services\SuratKeteranganPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/** Dashboard peserta: profil, riwayat lamaran, notifikasi, dan unduh surat balasan. */
class DashboardController extends Controller
{
    private const BERKAS = [
        'foto'  => 'foto_pas',
        'cv'    => 'cv_path',
        'surat' => 'surat_permohonan_path',
    ];

    public function index()
    {
        $pelamar = Auth::guard('pelamar')->user();

        $riwayat = $pelamar->lamaran()
            ->with(['lowongan' => fn ($q) => $q->withTrashed(), 'suratBalasan', 'dataMagang.suratKeterangan'])
            ->latest('id')
            ->get();

        $diterima = $riwayat->where('status', 'diterima')->values();

        return view('pelamar.dashboard', [
            'pelamar'  => $pelamar,
            'riwayat'  => $riwayat,
            'profil'   => $pelamar->profil(),                                  // data diri (diisi sekali, dipakai semua lamaran)
            'lengkap'  => $pelamar->profilLengkap(),
            'diterima' => $diterima,                                           // bahan notifikasi
            'ada'      => [                                                    // berkas mana yang pernah diunggah
                'cv'    => filled($pelamar->cv_path) || $riwayat->contains(fn ($l) => (bool) $l->cv_path),
                'surat' => filled($pelamar->surat_permohonan_path) || $riwayat->contains(fn ($l) => (bool) $l->surat_permohonan_path),
                'foto'  => (bool) $pelamar->fotoPath(),
            ],
            'sisa'     => max(0, \App\Models\Pelamar::MAKS_LAMARAN - $riwayat->count()),
            // Tab "Surat Keterangan": lamaran diterima beserta surat keterangan (bila sudah dibuat HRD)
            'keterangan' => $diterima,
            'keteranganTerbit' => $diterima->filter(fn ($l) => $l->dataMagang?->suratKeterangan?->terbit())->count(),
            'cfgKeterangan' => config('prakerin.surat_keterangan'),
        ]);
    }

    /** Berkas milik pelamar sendiri (disimpan di disk privat), diambil dari lamaran terbaru yang memilikinya. */
    public function berkas(string $jenis)
    {
        $kolom = self::BERKAS[$jenis] ?? abort(404);

        $pelamar = Auth::guard('pelamar')->user();
        $path    = $pelamar->{$kolom} ?: $pelamar->lamaran()->whereNotNull($kolom)->latest('id')->value($kolom);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    /** Foto profil untuk header & dashboard. */
    public function foto()
    {
        $path = Auth::guard('pelamar')->user()->fotoPath();

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    /** Menu foto di header -> "Ubah Foto Profil". */
    public function ubahFoto(Request $request)
    {
        $v = Validator::make($request->all(), [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image'    => 'Berkas harus berupa gambar (JPG/PNG).',
            'foto.mimes'    => 'Format foto harus JPG atau PNG.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
            'foto.uploaded' => 'Foto gagal diunggah (ukuran maksimal 2 MB).',
        ]);

        if ($v->fails()) {
            return back()->with('galat_foto', $v->errors()->first());
        }

        $pelamar = Auth::guard('pelamar')->user();
        $lama    = $pelamar->foto_profil;

        $pelamar->update(['foto_profil' => $request->file('foto')->store('pelamar/foto', 'local')]);

        if ($lama) {
            Storage::disk('local')->delete($lama);
        }

        return back()->with('sukses_foto', 'Foto profil berhasil diperbarui.');
    }

    /** Unduh surat balasan ijin prakerin (PDF) -- hanya untuk lamaran milik sendiri yang sudah diterima. */
    public function suratBalasan(Lamaran $lamaran)
    {
        abort_unless((int) $lamaran->pelamar_id === (int) Auth::guard('pelamar')->id(), 404);
        abort_unless($lamaran->status === 'diterima' && $lamaran->suratBalasan, 404);

        return SuratBalasanPdf::respons($lamaran->suratBalasan);
    }

    /** Unduh Surat Keterangan selesai prakerin (PDF) -- hanya milik sendiri dan sudah diterbitkan HRD. */
    public function suratKeterangan(SuratKeterangan $surat)
    {
        $milikSendiri = Lamaran::where('pelamar_id', Auth::guard('pelamar')->id())
            ->where('data_magang_id', $surat->data_magang_id)
            ->exists();

        abort_unless($milikSendiri && $surat->terbit() && $surat->lengkap(), 404);

        return SuratKeteranganPdf::respons($surat);
    }
}