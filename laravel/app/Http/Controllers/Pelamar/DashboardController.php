<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Services\SuratBalasanPdf;
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
            ->with(['lowongan' => fn ($q) => $q->withTrashed(), 'suratBalasan'])
            ->latest('id')
            ->get();

        return view('pelamar.dashboard', [
            'pelamar'  => $pelamar,
            'riwayat'  => $riwayat,
            'profil'   => $riwayat->first(),                                   // data diri = lamaran terbaru
            'diterima' => $riwayat->where('status', 'diterima')->values(),     // bahan notifikasi
            'ada'      => [                                                    // berkas mana yang pernah diunggah
                'cv'    => $riwayat->contains(fn ($l) => (bool) $l->cv_path),
                'surat' => $riwayat->contains(fn ($l) => (bool) $l->surat_permohonan_path),
                'foto'  => (bool) $pelamar->fotoPath(),
            ],
            'sisa'     => max(0, \App\Models\Pelamar::MAKS_LAMARAN - $riwayat->count()),
        ]);
    }

    /** Berkas milik pelamar sendiri (disimpan di disk privat), diambil dari lamaran terbaru yang memilikinya. */
    public function berkas(string $jenis)
    {
        $kolom = self::BERKAS[$jenis] ?? abort(404);

        $path = Auth::guard('pelamar')->user()
            ->lamaran()->whereNotNull($kolom)->latest('id')->value($kolom);

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
}
