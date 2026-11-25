<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMagang;
use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hariIni = today();

        // Peserta aktif: definisi yang SAMA dengan halaman Peserta Aktif (scope DataMagang::aktif()).
        DataMagang::tandaiSelesai();
        $aktifTotal  = DataMagang::aktif()->count();
        $aktifSmk    = DataMagang::aktif()->where('tipe_magang', 'smk')->count();
        $aktifKuliah = DataMagang::aktif()->where('tipe_magang', 'kuliah')->count();

        // Presensi hari ini: dihitung per orang (bukan per sesi pagi/sore)
        $sesi = fn () => DB::table('sesi_presensi')->whereDate('tanggal', $hariIni);

        $hadir    = $sesi()->where('status', 'hadir')->where('status_persetujuan', '!=', 'ditolak')->distinct()->count('user_id');
        $approved = $sesi()->where('status', 'hadir')->whereIn('status_persetujuan', ['auto', 'disetujui'])->distinct()->count('user_id');
        $izin     = $sesi()->whereIn('status', ['izin', 'sakit'])->where('status_persetujuan', '!=', 'ditolak')->distinct()->count('user_id');
        $alpha    = $sesi()->where('status', 'alpha')->distinct()->count('user_id');
        $pending  = $sesi()->where('status_persetujuan', 'menunggu')->count();

        return view('admin.dashboard', [
            'stat' => [
                'lowongan_dibuka'  => Lowongan::dibuka()->count(),
                'lamaran_menunggu' => Lamaran::where('status', 'menunggu')->count(),
                'aktif_total'      => $aktifTotal,
                'aktif_smk'        => $aktifSmk,
                'aktif_kuliah'     => $aktifKuliah,
                'hadir'            => $hadir,
                'pending'          => $pending,
            ],
            'presensi' => compact('approved', 'izin', 'alpha', 'pending'),
            'terbaru'  => Lamaran::with(['lowongan' => fn ($q) => $q->withTrashed(), 'divisi', 'dataMagang'])
                ->where('status', 'menunggu')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}