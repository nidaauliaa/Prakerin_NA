<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\HariLibur;
use App\Models\SesiPresensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class MonitoringApiController extends ApiController
{
    // GET /api/v1/ping  -> cek token dan koneksi
    public function ping(Request $r): JsonResponse
    {
        return $this->ok([
            'layanan' => config('app.name'),
            'versi_api' => 'v1',
            'waktu_server' => now()->toIso8601String(),
            'pemilik_token' => $r->attributes->get('api_token')->nama,
        ]);
    }

    // GET /api/v1/sekolah  (daftar asal sekolah/kampus dari data_magang + jumlah pesertanya)
    public function sekolah(): JsonResponse
    {
        $data = DataMagang::selectRaw('TRIM(asal_sekolah) as nama, COUNT(*) as jumlah_peserta')
            ->whereNotNull('asal_sekolah')
            ->whereRaw("TRIM(asal_sekolah) <> ''")
            ->groupByRaw('TRIM(asal_sekolah)')
            ->orderBy('nama')
            ->get()
            ->map(fn ($s) => [
                'nama' => $s->nama,
                'jumlah_peserta' => (int) $s->jumlah_peserta,
            ])->values();

        return $this->ok(['data' => $data]);
    }

    // GET /api/v1/hari-libur?tahun=2026  -> kalender kerja: hari libur + hari kerja pengganti
    public function hariLibur(Request $r): JsonResponse
    {
        $tahun = (int) $r->query('tahun', today()->year);

        $data = HariLibur::with('pengganti')
            ->where(fn ($q) => $q->whereYear('tanggal_mulai', $tahun)->orWhereYear('tanggal_selesai', $tahun))
            ->orderBy('tanggal_mulai')
            ->get()
            ->map(fn ($h) => [
                'nama' => $h->nama,
                'jenis' => $h->labelJenis(),
                'tanggal_mulai' => $h->tanggal_mulai->toDateString(),
                'tanggal_selesai' => $h->tanggal_selesai->toDateString(),
                'lama_hari' => $h->lamaHari(),
                'keterangan' => $h->keterangan,
                'hari_pengganti' => $h->pengganti->map(fn ($p) => $p->tanggal->toDateString())->values(),
            ])->values();

        return $this->ok(['tahun' => $tahun, 'data' => $data]);
    }

    // GET /api/v1/departemen
    public function departemen(): JsonResponse
    {
        $data = Departemen::withCount('dataMagang')->orderBy('nama')->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'nama' => $d->nama,
                'kuota' => $d->kuota,
                'jumlah_peserta' => $d->data_magang_count,
            ])->values();

        return $this->ok(['data' => $data]);
    }

    // GET /api/v1/presensi?tanggal=2026-10-07   (semua peserta pada satu hari; default hari ini)
    public function presensiHarian(Request $r): JsonResponse
    {
        $v = Validator::make($r->query(), ['tanggal' => 'nullable|date_format:Y-m-d']);
        if ($v->fails()) {
            $this->gagal($v->errors()->first(), 422);
        }
        $tanggal = $r->query('tanggal', Carbon::today()->toDateString());

        $data = SesiPresensi::with('user.dataMagang:id,user_id,nis_nim,nama')
            ->where('tanggal', $tanggal)
            ->orderBy('waktu_absen')
            ->get()
            ->map(fn ($s) => [
                'nis_nim' => $s->user?->dataMagang?->nis_nim,
                'nama' => $s->user?->dataMagang?->nama ?? $s->user?->name,
                'sesi' => $s->sesi,
                'waktu_absen' => $s->waktu_absen?->toIso8601String(),
                'status' => $s->status,
                'status_persetujuan' => $s->status_persetujuan,
                'lokasi' => $s->lokasi,
            ])->values();

        return $this->ok(['tanggal' => $tanggal, 'total' => $data->count(), 'data' => $data]);
    }

    // GET /api/v1/rekap?bulan=2026-10   (jumlah HARI per status untuk tiap peserta; default bulan ini)
    public function rekapBulanan(Request $r): JsonResponse
    {
        // Tanpa parameter apa pun = bulan ini. Kalau ada dari/sampai, 'bulan' TIDAK boleh diisi otomatis,
        // kalau tidak rentang dari/sampai selalu tertimpa seluruh bulan (QC-149, QC-150).
        if (! $r->filled('bulan') && ! $r->filled('dari') && ! $r->filled('sampai')) {
            $r->query->set('bulan', Carbon::today()->format('Y-m'));
        }
        [$dari, $sampai] = $this->rentang($r);
        $bulan = $r->filled('bulan') ? $r->query('bulan') : null;

        $hitung = SesiPresensi::selectRaw('user_id, status, COUNT(DISTINCT tanggal) as n')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id');

        $data = DataMagang::with(['departemen:id,nama'])
            ->whereNotNull('user_id')
            ->orderBy('nama')
            ->get()
            ->map(function ($d) use ($hitung) {
                $per = ($hitung[$d->user_id] ?? collect())->pluck('n', 'status');

                return [
                    'nis_nim' => $d->nis_nim,
                    'nama' => $d->nama,
                    'sekolah' => $d->asal_sekolah,
                    'departemen' => $d->departemen?->nama,
                    'hadir' => (int) ($per['hadir'] ?? 0),
                    'izin' => (int) ($per['izin'] ?? 0),
                    'sakit' => (int) ($per['sakit'] ?? 0),
                    'alpha' => (int) ($per['alpha'] ?? 0),
                    'cuti' => (int) ($per['cuti'] ?? 0),
                ];
            })->values();

        return $this->ok(['bulan' => $bulan, 'rentang' => ['dari' => $dari, 'sampai' => $sampai], 'data' => $data]);
    }
}