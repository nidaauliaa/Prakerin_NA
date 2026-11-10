<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\LogLokasiCuriga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LokasiCurigaController extends Controller
{
    public function index(Request $r)
    {
        $r->validate([
            'tindakan' => ['nullable', 'in:diblokir,ditahan'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'departemen_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        // Bawaan: 7 hari terakhir.
        $dari = $r->filled('dari') ? $r->input('dari') : now()->subDays(6)->toDateString();
        $sampai = $r->filled('sampai') ? $r->input('sampai') : now()->toDateString();

        $dasar = LogLokasiCuriga::query()
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai)
            ->when($r->filled('tindakan'), fn ($q) => $q->where('tindakan', $r->input('tindakan')))
            ->when($r->filled('departemen_id'), fn ($q) => $q->whereHas(
                'user',
                fn ($u) => $u->where('departemen_id', $r->input('departemen_id'))
            ))
            ->when($r->filled('q'), function ($q) use ($r) {
                $kata = '%' . $r->input('q') . '%';
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $kata)->orWhere('nis_nim', 'like', $kata));
            });

        $stat = [
            'total' => (clone $dasar)->count(),
            'diblokir' => (clone $dasar)->where('tindakan', 'diblokir')->count(),
            'ditahan' => (clone $dasar)->where('tindakan', 'ditahan')->count(),
            'peserta' => (clone $dasar)->whereNotNull('user_id')->distinct()->count('user_id'),
        ];

        // Peserta yang paling sering muncul pada periode ini.
        $teratas = (clone $dasar)
            ->whereNotNull('user_id')
            ->select(
                'user_id',
                DB::raw("SUM(CASE WHEN tindakan = 'diblokir' THEN 1 ELSE 0 END) AS jml_diblokir"),
                DB::raw("SUM(CASE WHEN tindakan = 'ditahan' THEN 1 ELSE 0 END) AS jml_ditahan"),
                DB::raw('COUNT(*) AS jml_total')
            )
            ->groupBy('user_id')
            ->orderByDesc('jml_total')
            ->limit(5)
            ->with('user')
            ->get();

        $log = $dasar
            ->with(['user.departemen'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        // Kejadian yang tampil di halaman ini dianggap sudah dilihat -> angka merah di menu hilang.
        // Hanya baris yang benar-benar tampil (halaman & filter saat ini) yang ditandai.
        // Baris tetap membawa penanda "baru" untuk tampilan kali ini karena sudah terambil dari database.
        try {
            $idBaru = $log->getCollection()->whereNull('dilihat_admin_at')->pluck('id');
            if ($idBaru->isNotEmpty()) {
                LogLokasiCuriga::whereIn('id', $idBaru)->update(['dilihat_admin_at' => now()]);
            }
        } catch (\Throwable $e) {
            // kolom dilihat_admin_at belum dibuat (SQL belum dijalankan): halaman tetap tampil
        }

        return view('admin.lokasi-curiga.index', [
            'log' => $log,
            'stat' => $stat,
            'teratas' => $teratas,
            'departemen' => Departemen::orderBy('nama')->get(),
            'dari' => $dari,
            'sampai' => $sampai,
        ]);
    }
}