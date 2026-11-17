<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratKeterangan;
use App\Services\SinkronSuratKeterangan;
use App\Services\SuratKeteranganPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * HRD melengkapi (nomor, tanggal, penempatan) lalu menerbitkan Surat Keterangan peserta.
 * Nilainya sudah terisi otomatis dari form penilaian (lihat SinkronSuratKeterangan).
 */
class SuratKeteranganController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->status, ['draft', 'terbit'], true) ? $request->status : 'semua';

        $surat = SuratKeterangan::with('dataMagang.departemen')
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.surat-keterangan.index', [
            'surat'  => $surat,
            'status' => $status,
            'hitung' => [
                'draft' => SuratKeterangan::where('status', 'draft')->count(),
                'terbit' => SuratKeterangan::where('status', 'terbit')->count(),
            ],
            'rows'   => $surat->getCollection()->map(fn (SuratKeterangan $s) => [
                'id'            => $s->id,
                'nama'          => $s->dataMagang->nama,
                'nomor_surat'   => $s->nomor_surat ?? '',
                'tanggal_surat' => ($s->tanggal_surat ?? today())->toDateString(),
                'penempatan'    => $s->penempatan ?? '',
                'tgl_mulai'     => $s->tgl_mulai?->toDateString() ?? '',
                'tgl_selesai'   => $s->tgl_selesai?->toDateString() ?? '',
                'url'           => route('admin.surat-keterangan.simpan', $s),
            ])->values(),
        ]);
    }

    public function simpan(Request $request, SuratKeterangan $surat)
    {
        $data = $request->validate([
            'nomor_surat'   => ['required', 'string', 'max:30'],
            'tanggal_surat' => ['required', 'date'],
            'penempatan'    => ['required', 'string', 'max:150'],
            'tgl_mulai'     => ['required', 'date'],
            'tgl_selesai'   => ['required', 'date', 'after_or_equal:tgl_mulai'],
        ], [
            'nomor_surat.required'       => 'Nomor surat wajib diisi.',
            'tanggal_surat.required'     => 'Tanggal surat wajib diisi.',
            'penempatan.required'        => 'Penempatan wajib diisi.',
            'tgl_mulai.required'         => 'Tanggal mulai wajib diisi.',
            'tgl_selesai.required'       => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $surat->update($data);

        return back()->with('sukses', "Data surat {$surat->dataMagang->nama} disimpan.");
    }

    /** Ambil ulang nilai dari penilaian terbaru (berguna untuk surat yang sudah terbit). */
    public function sinkron(SuratKeterangan $surat, SinkronSuratKeterangan $sinkron)
    {
        $sinkron->jalankan($surat->dataMagang, paksa: true);

        return back()->with('sukses', "Nilai surat {$surat->dataMagang->nama} diperbarui dari data penilaian.");
    }

    public function terbitkan(SuratKeterangan $surat)
    {
        if (! $surat->lengkap()) {
            return back()->withErrors(['surat' => 'Lengkapi nomor, tanggal, penempatan, dan periode surat lebih dulu (tombol Edit).']);
        }

        $surat->update([
            'status'           => 'terbit',
            'diterbitkan_pada' => now(),
            'diterbitkan_oleh' => config('auth.guards.admin') ? Auth::guard('admin')->id() : null,
        ]);

        return back()->with('sukses', "Surat keterangan {$surat->dataMagang->nama} diterbitkan. Peserta bisa melihat & mengunduhnya dari Dashboard Peserta.");
    }

    public function tarik(SuratKeterangan $surat)
    {
        $surat->update(['status' => 'draft', 'diterbitkan_pada' => null, 'diterbitkan_oleh' => null]);

        return back()->with('sukses', "Surat {$surat->dataMagang->nama} ditarik kembali menjadi draft.");
    }

    public function pdf(SuratKeterangan $surat)
    {
        if (! $surat->lengkap()) {
            return back()->withErrors(['surat' => 'Pratinjau PDF baru bisa dibuka setelah nomor, tanggal, penempatan, dan periode surat diisi.']);
        }

        return SuratKeteranganPdf::respons($surat, inline: true);
    }
}
