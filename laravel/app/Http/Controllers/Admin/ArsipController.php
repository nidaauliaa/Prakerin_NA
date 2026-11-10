<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\KalenderKerja;
use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\LaporanHarian;
use App\Models\LogPerubahanStatus;
use App\Models\SesiPresensi;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Arsip peserta yang sudah tidak aktif (status bukan ACTIVE, atau tanggal selesai magang sudah lewat):
 * data diri, presensi, tugas, dan laporan harian selama magang.
 */
class ArsipController extends Controller
{
    public function index(Request $r)
    {
        DataMagang::tandaiSelesai(); // status ACTIVE yang masa magangnya sudah lewat -> SELESAI (QC-108)

        $data = DataMagang::tidakAktif()
            ->with(['departemen', 'user'])
            ->when($r->q, function ($x, $v) {
                $x->where(fn ($w) => $w->where('nama', 'like', "%{$v}%")->orWhere('nis_nim', 'like', "%{$v}%"));
            })
            ->when($r->departemen_id, fn ($x, $v) => $x->where('departemen_id', $v))
            ->when($r->tipe, fn ($x, $v) => $x->where('tipe_magang', $v))
            ->orderByDesc('tgl_selesai')->orderBy('nama')
            ->paginate(50)->withQueryString();

        // Ringkasan isi arsip per peserta di halaman ini (3 query, bukan per baris).
        $userIds = $data->pluck('user_id')->filter()->values();
        $hariPresensi = SesiPresensi::whereIn('user_id', $userIds)->select('user_id', DB::raw('COUNT(DISTINCT tanggal) AS n'))->groupBy('user_id')->pluck('n', 'user_id');
        $jumlahTugas = Tugas::whereIn('user_id', $userIds)->select('user_id', DB::raw('COUNT(*) AS n'))->groupBy('user_id')->pluck('n', 'user_id');
        $jumlahLaporan = LaporanHarian::join('sesi_presensi', 'sesi_presensi.id', '=', 'laporan_harian.sesi_presensi_id')
            ->whereIn('sesi_presensi.user_id', $userIds)
            ->select('sesi_presensi.user_id', DB::raw('COUNT(*) AS n'))->groupBy('sesi_presensi.user_id')->pluck('n', 'user_id');

        return view('admin.arsip.index', [
            'data' => $data,
            'departemen' => Departemen::orderBy('nama')->get(),
            'total' => DataMagang::tidakAktif()->count(),
            'hariPresensi' => $hariPresensi,
            'jumlahTugas' => $jumlahTugas,
            'jumlahLaporan' => $jumlahLaporan,
        ]);
    }

    public function show(DataMagang $dataMagang)
    {
        DataMagang::tandaiSelesai();
        $dataMagang->refresh();
        $dataMagang->load(['departemen', 'user']);
        $user = $dataMagang->user;

        $sesi = $user
            ? SesiPresensi::with('laporanHarian')->where('user_id', $user->id)->orderByDesc('tanggal')->orderBy('sesi')->get()
            : collect();
        $tugas = $user ? Tugas::where('user_id', $user->id)->latest()->get() : collect();
        $log = $user ? LogPerubahanStatus::where('user_id', $user->id)->orderBy('id')->get() : collect();

        return view('admin.arsip.show', [
            'dm' => $dataMagang,
            'user' => $user,
            'sesi' => $sesi,
            'tugas' => $tugas,
            'laporan' => $sesi->filter(fn ($s) => $s->laporanHarian)->values(),
            'rekap' => $this->rekap($dataMagang, $sesi, $log),
        ]);
    }

    /** Foto selfie presensi (admin memakai guard terpisah, jadi tidak lewat rute foto peserta). */
    public function foto(SesiPresensi $sesiPresensi)
    {
        abort_if(
            blank($sesiPresensi->foto_url) || ! Storage::disk('local')->exists($sesiPresensi->foto_url),
            404
        );

        return response()->file(Storage::disk('local')->path($sesiPresensi->foto_url));
    }

    public function unduhTugas(Tugas $tugas)
    {
        abort_unless(Storage::disk('local')->exists($tugas->path_file), 404, 'Berkas tugas tidak ditemukan.');

        if ($tugas->dilihat_admin_at === null) {
            $tugas->forceFill(['dilihat_admin_at' => now()])->save();
        }

        return Storage::disk('local')->download($tugas->path_file, $tugas->nama_file_asli);
    }

    /**
     * Rekap kehadiran selama seluruh masa magang. Hari kerja = menurut Kalender Kerja (Senin-Jumat, tanpa libur, plus pengganti) dari tgl_mulai
     * (atau presensi pertama) sampai tgl_selesai (atau hari ini / presensi terakhir).
     * Aturan status harian sama dengan halaman presensi admin: log admin menang; selain itu
     * sesi alpha > izin/sakit/cuti > hadir.
     */
    private function rekap(DataMagang $dm, $sesi, $log): array
    {
        $r = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0, 'alpha' => 0, 'tanpa_data' => 0, 'hari_kerja' => 0];

        $mulai = $dm->tgl_mulai ?? ($sesi->isNotEmpty() ? $sesi->min('tanggal') : null);
        $akhir = $dm->tgl_selesai ?? ($sesi->isNotEmpty() ? $sesi->max('tanggal') : null);

        if (! $mulai || ! $akhir) {
            $r['persen'] = null;

            return $r;
        }

        $batas = $akhir->copy()->startOfDay()->min(today());

        $logTgl = [];
        foreach ($log as $l) {
            $logTgl[$l->tanggal->toDateString()] = $l->status_baru;
        }
        $sesiTgl = $sesi->groupBy(fn ($s) => $s->tanggal->toDateString());

        for ($d = $mulai->copy()->startOfDay(); $d->lte($batas); $d->addDay()) {
            if (! KalenderKerja::hariKerja($d)) {
                continue;
            }
            $k = $d->toDateString();
            $st = $logTgl[$k] ?? null;
            if (! $st) {
                $hari = $sesiTgl->get($k, collect());
                if ($hari->contains(fn ($s) => $s->status === 'alpha')) {
                    $st = 'alpha';
                } elseif ($x = $hari->first(fn ($s) => $s->status !== 'hadir')) {
                    $st = $x->status;
                } elseif ($hari->isNotEmpty()) {
                    $st = 'hadir';
                }
            }
            $r['hari_kerja']++;
            $r[$st ?? 'tanpa_data']++;
        }

        $r['persen'] = $r['hari_kerja'] > 0 ? (int) round($r['hadir'] / $r['hari_kerja'] * 100) : null;

        return $r;
    }
}
