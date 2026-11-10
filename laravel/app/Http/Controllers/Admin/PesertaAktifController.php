<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Daftar peserta yang masih aktif: data_magang berstatus ACTIVE dan tanggal selesainya belum lewat.
 */
class PesertaAktifController extends Controller
{
    public function index(Request $r)
    {
        DataMagang::tandaiSelesai();

        // Filter dipakai bersama oleh daftar DAN kartu statistik, supaya angka di kartu ikut berubah
        // saat kata kunci / departemen / tipe magang difilter (QC-107).
        $saring = fn () => DataMagang::aktif()
            ->when($r->q, function ($x, $v) {
                $x->where(fn ($w) => $w->where('nama', 'like', "%{$v}%")->orWhere('nis_nim', 'like', "%{$v}%"));
            })
            ->when($r->departemen_id, fn ($x, $v) => $x->where('departemen_id', $v))
            ->when($r->tipe, fn ($x, $v) => $x->where('tipe_magang', $v));

        $q = $saring()->with(['departemen', 'user'])->orderBy('nama');

        $hariIni = today()->toDateString();

        // Setelah "Tambah Anak Magang" berhasil, popup QR peserta baru langsung terbuka di halaman ini.
        $qrBaru = null;
        if ($id = session('qr_peserta_id')) {
            $u = User::with('departemen')->where('peran', 'peserta')->find($id);
            $qrBaru = $u ? [
                'nama' => $u->name,
                'nis_nim' => $u->nis_nim,
                'departemen' => optional($u->departemen)->nama ?? '-',
                'inisial' => collect(preg_split('/\s+/', $u->name))->filter()->map(fn ($x) => strtoupper($x[0]))->take(2)->implode(''),
                'qr_url' => route('admin.peserta.qr.image', $u),
            ] : null;
        }

        return view('admin.peserta-aktif.index', [
            'qrBaru' => $qrBaru,
            'data' => $q->paginate(50)->withQueryString(),
            'departemen' => Departemen::orderBy('nama')->get(),
            'stat' => [
                'aktif' => $saring()->count(),
                'punya_akun' => $saring()->whereNotNull('user_id')->count(),
                'belum_akun' => $saring()->whereNull('user_id')->count(),
                'segera_selesai' => $saring()->whereNotNull('tgl_selesai')
                    ->whereBetween('tgl_selesai', [$hariIni, today()->addDays(30)->toDateString()])->count(),
            ],
        ]);
    }
}
