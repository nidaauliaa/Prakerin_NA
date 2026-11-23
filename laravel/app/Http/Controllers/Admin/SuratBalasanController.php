<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\SuratBalasan;
use App\Services\SuratBalasanPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** HRD membuat / mengubah Surat Balasan Ijin Prakerin untuk lamaran yang sudah diterima. */
class SuratBalasanController extends Controller
{
    public function simpan(Request $request, Lamaran $lamaran)
    {
        if ($lamaran->status !== 'diterima') {
            return back()->withErrors(['surat' => 'Surat balasan hanya bisa dibuat untuk lamaran yang sudah diterima.']);
        }

        $data = $request->validate([
            'nomor_surat'      => ['required', 'string', 'max:30'],
            'tanggal_surat'    => ['required', 'date'],
            'ditujukan_kepada' => ['required', 'string', 'max:150'],
            'tgl_pengajuan'    => ['required', 'date'],
            'media_pengajuan'  => ['required', 'in:Website Prakerin,Pengajuan Sekolah'],
            'tgl_mulai'        => ['required', 'date'],
            'tgl_selesai'      => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'tgl_induction'    => ['required', 'date'],
        ], [
            'nomor_surat.required'      => 'Nomor surat wajib diisi.',
            'tanggal_surat.required'    => 'Tanggal surat wajib diisi.',
            'ditujukan_kepada.required' => 'Kolom "Ditujukan kepada" wajib diisi.',
            'tgl_pengajuan.required'    => 'Tanggal pengajuan wajib diisi.',
            'media_pengajuan.required'  => 'Media pengajuan wajib dipilih.',
            'media_pengajuan.in'        => 'Media pengajuan harus Website Prakerin atau Pengajuan Sekolah.',
            'tgl_mulai.required'        => 'Tanggal mulai pelaksanaan wajib diisi.',
            'tgl_selesai.required'      => 'Tanggal selesai pelaksanaan wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'tgl_induction.required'    => 'Tanggal induction wajib diisi.',
        ]);

        $baru = ! $lamaran->suratBalasan()->exists();

        SuratBalasan::updateOrCreate(
            ['lamaran_id' => $lamaran->id],
            $data + ['dibuat_oleh' => config('auth.guards.admin') ? Auth::guard('admin')->id() : null]
        );

        return redirect()
            ->route('admin.lamaran.index', ['status' => 'diterima', 'buka' => $lamaran->id])
            ->with('sukses', $baru
                ? "Surat balasan untuk {$lamaran->nama} dibuat. Pelamar kini bisa mengunduhnya (PDF) dari Dashboard Peserta."
                : "Surat balasan untuk {$lamaran->nama} diperbarui.");
    }

    /** Pratinjau PDF untuk HRD (tampil di tab baru). */
    public function pdf(Lamaran $lamaran)
    {
        $surat = $lamaran->suratBalasan;
        abort_unless($surat, 404);

        return SuratBalasanPdf::respons($surat, inline: true);
    }
}
