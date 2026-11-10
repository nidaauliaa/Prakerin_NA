<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Monitoring tugas yang diunggah peserta magang (hanya melihat/unduh + filter). */
class TugasAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = Tugas::with('user.departemen')->orderByDesc('created_at');

        if ($r->filled('q')) {
            $kata = '%' . $r->query('q') . '%';
            $q->where(function ($w) use ($kata) {
                $w->where('nama_file_asli', 'like', $kata)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $kata)->orWhere('nis_nim', 'like', $kata));
            });
        }
        if ($r->filled('departemen_id')) {
            $q->whereHas('user', fn ($u) => $u->where('departemen_id', $r->query('departemen_id')));
        }
        if ($r->query('jenis') === 'gambar') {
            $q->where('tipe_mime', 'like', 'image/%');
        } elseif ($r->query('jenis') === 'pdf') {
            $q->where('tipe_mime', 'application/pdf');
        } elseif ($r->query('jenis') === 'dokumen') {
            $q->where('tipe_mime', 'not like', 'image/%')->where('tipe_mime', '!=', 'application/pdf');
        }
        if ($r->query('status') === 'baru') {
            $q->whereNull('dilihat_admin_at');
        } elseif ($r->query('status') === 'dilihat') {
            $q->whereNotNull('dilihat_admin_at');
        }
        if ($r->filled('dari')) {
            $q->whereDate('created_at', '>=', $r->query('dari'));
        }
        if ($r->filled('sampai')) {
            $q->whereDate('created_at', '<=', $r->query('sampai'));
        }

        return view('admin.tugas.index', [
            'tugas' => $q->paginate(30)->withQueryString(),
            'departemen' => Departemen::orderBy('nama')->get(),
            'stat' => [
                'total' => Tugas::count(),
                'baru' => Tugas::whereNull('dilihat_admin_at')->count(),
                'hari_ini' => Tugas::whereDate('created_at', today())->count(),
                'peserta' => Tugas::distinct('user_id')->count('user_id'),
            ],
        ]);
    }

    /** ?lihat=1 membuka gambar/PDF langsung di tab baru; selain itu diunduh. */
    public function unduh(Request $r, Tugas $tugas)
    {
        abort_unless(Storage::disk('local')->exists($tugas->path_file), 404, 'Berkas tugas tidak ditemukan.');

        if ($tugas->dilihat_admin_at === null) {
            $tugas->forceFill(['dilihat_admin_at' => now()])->save();
        }

        $bolehInline = in_array($tugas->tipe_mime, ['image/jpeg', 'image/png', 'application/pdf'], true);

        if ($r->boolean('lihat') && $bolehInline) {
            return response()->file(Storage::disk('local')->path($tugas->path_file), [
                'Content-Type' => $tugas->tipe_mime,
                'Content-Disposition' => 'inline; filename="' . addslashes($tugas->nama_file_asli) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return Storage::disk('local')->download($tugas->path_file, $tugas->nama_file_asli);
    }
}