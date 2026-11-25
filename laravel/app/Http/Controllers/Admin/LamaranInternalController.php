<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pelamar;
use App\Services\PesertaInternalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Kelola Lamaran Masuk > "Tambah Peserta Internal".
 * Peserta internal membuat akun & mengisi profil sendiri lebih dulu; HRD mencarinya (nama / NIS-NIM),
 * lalu cukup memilih posisi (section) dan periode.
 */
class LamaranInternalController extends Controller
{
    /** Langkah 1: cari peserta. */
    public function create(Request $request)
    {
        $q     = trim((string) $request->query('q'));
        $hasil = collect();

        if ($q !== '') {
            $like  = '%' . addcslashes($q, '%_\\') . '%';
            $hasil = Pelamar::where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('nis_nim', 'like', $like))
                ->orderBy('nama')->limit(20)->get()
                ->each(function (Pelamar $p) {
                    $p->setAttribute('status_internal', $p->sudahDiterima() ? 'diterima' : ($p->profilLengkap() ? 'siap' : 'belum_lengkap'));
                });
        }

        return view('admin.lamaran.internal', compact('q', 'hasil'));
    }

    /** Langkah 2: pilih posisi (section) & periode. */
    public function form(Pelamar $pelamar, PesertaInternalService $layanan)
    {
        try {
            $layanan->pastikanBisaDimasukkan($pelamar);
        } catch (ValidationException $e) {
            return redirect()->route('admin.lamaran.internal.create', ['q' => $pelamar->nis_nim ?: $pelamar->nama])
                ->withErrors($e->errors());
        }

        return view('admin.lamaran.internal-form', [
            'pelamar'    => $pelamar,
            'divisi'     => Divisi::with('departemen:id,nama')->get(['id', 'departemen_id', 'nama'])
                ->sortBy(fn ($d) => ($d->departemen?->nama ?? '') . '|' . $d->nama)->values(),
        ]);
    }

    public function store(Request $request, Pelamar $pelamar, PesertaInternalService $layanan)
    {
        $data = $request->validate([
            'divisi_id'     => ['required', 'integer', 'exists:divisi,id'],
            // Periode bebas: tidak ada batas minimal durasi untuk jalur internal.
            'tgl_mulai'     => ['required', 'date'],
            'tgl_selesai'   => ['required', 'date', 'after:tgl_mulai'],
        ], [
            'divisi_id.required'     => 'Posisi (section) wajib dipilih.',
            'tgl_mulai.required'     => 'Tanggal mulai wajib diisi.',
            'tgl_selesai.required'   => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.after'      => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        try {
            $hasil = $layanan->buat(
                $pelamar,
                (int) $data['divisi_id'],
                $data['tgl_mulai'],
                $data['tgl_selesai'],
                config('auth.guards.admin') ? Auth::guard('admin')->id() : null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('admin.lamaran.index', ['status' => 'diterima', 'buka' => $hasil['lamaran']->id, 'surat' => 1])
            ->with('sukses', "{$pelamar->nama} ditambahkan sebagai peserta internal. Presensi: login NIS/NIM {$pelamar->nis_nim} dengan kata sandi akun portalnya. "
                . ($hasil['otomatis'] ? "{$hasil['otomatis']} lamaran lain miliknya otomatis ditolak. " : '')
                . 'Langkah berikutnya: isi form Surat Balasan Ijin Prakerin di bawah ini.');
    }
}
