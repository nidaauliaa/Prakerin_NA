<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DataMagang;
use App\Models\LaporanHarian;
use App\Models\SesiPresensi;
use App\Models\Tugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PesertaApiController extends ApiController
{
    // GET /api/v1/peserta
    public function index(Request $r): JsonResponse
    {
        $v = Validator::make($r->query(), [
            'sekolah' => 'nullable|string|max:150',
            'departemen_id' => 'nullable|integer',
            'status' => 'nullable|string|max:20',
            'tipe' => 'nullable|in:smk,kuliah',
            'q' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:200',
            'page' => 'nullable|integer|min:1',
        ]);
        if ($v->fails()) {
            $this->gagal($v->errors()->first(), 422);
        }

        $q = DataMagang::with(['departemen:id,nama']);

        if ($r->filled('sekolah')) {
            $q->where('asal_sekolah', $r->query('sekolah'));
        }
        if ($r->filled('departemen_id')) {
            $q->where('departemen_id', $r->query('departemen_id'));
        }
        if ($r->filled('status')) {
            $q->where('status_magang', $r->query('status'));
        }
        if ($r->filled('tipe')) {
            $q->where('tipe_magang', $r->query('tipe'));
        }
        if ($r->filled('q')) {
            $kata = '%' . $r->query('q') . '%';
            $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('nis_nim', 'like', $kata));
        }

        $p = $q->orderBy('nama')->paginate((int) $r->query('per_page', 50));

        return $this->ok([
            'data' => $p->getCollection()->map(fn ($d) => $this->ringkas($d))->values(),
            'meta' => [
                'halaman' => $p->currentPage(),
                'per_halaman' => $p->perPage(),
                'total' => $p->total(),
                'halaman_terakhir' => $p->lastPage(),
            ],
        ]);
    }

    // GET /api/v1/peserta/{nis_nim}
    public function show(string $nomor): JsonResponse
    {
        return $this->ok(['data' => $this->ringkas($this->cari($nomor))]);
    }

    // GET /api/v1/peserta/{nis_nim}/presensi
    public function presensi(Request $r, string $nomor): JsonResponse
    {
        $d = $this->cari($nomor);
        [$dari, $sampai] = $this->rentang($r);
        $dasar = ['peserta' => $this->ringkas($d), 'rentang' => ['dari' => $dari, 'sampai' => $sampai]];

        if (! $d->user_id) {
            return $this->ok($dasar + ['data' => [], 'catatan' => 'Akun peserta belum ditautkan, belum ada data presensi.']);
        }

        $baris = SesiPresensi::where('user_id', $d->user_id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')->orderBy('sesi')
            ->get()
            ->map(fn ($s) => [
                'tanggal' => $s->tanggal->toDateString(),
                'sesi' => $s->sesi,
                'waktu_absen' => $s->waktu_absen?->toIso8601String(),
                'status' => $s->status,
                'status_persetujuan' => $s->status_persetujuan,
                'lokasi' => $s->lokasi,
                'jarak_meter' => $s->jarak_meter !== null ? (float) $s->jarak_meter : null,
            ])->values();

        return $this->ok($dasar + ['data' => $baris]);
    }

    // GET /api/v1/peserta/{nis_nim}/laporan
    public function laporan(Request $r, string $nomor): JsonResponse
    {
        $d = $this->cari($nomor);
        [$dari, $sampai] = $this->rentang($r);
        $dasar = ['peserta' => $this->ringkas($d), 'rentang' => ['dari' => $dari, 'sampai' => $sampai]];

        if (! $d->user_id) {
            return $this->ok($dasar + ['data' => [], 'catatan' => 'Akun peserta belum ditautkan, belum ada laporan.']);
        }

        $baris = LaporanHarian::with('sesiPresensi:id,tanggal,sesi')
            ->whereHas('sesiPresensi', fn ($q) => $q->where('user_id', $d->user_id)->whereBetween('tanggal', [$dari, $sampai]))
            ->get()
            ->sortBy(fn ($l) => $l->sesiPresensi->tanggal->toDateString() . $l->sesiPresensi->sesi)
            ->map(fn ($l) => [
                'tanggal' => $l->sesiPresensi->tanggal->toDateString(),
                'sesi' => $l->sesiPresensi->sesi,
                'laporan' => $l->laporan,
                'status_review' => $l->status_review,
                'direview_pada' => $l->direview_pada?->toIso8601String(),
            ])->values();

        return $this->ok($dasar + ['data' => $baris]);
    }

    // GET /api/v1/peserta/{nis_nim}/tugas  (metadata saja, file tidak bisa diunduh lewat API)
    public function tugas(string $nomor): JsonResponse
    {
        $d = $this->cari($nomor);
        $dasar = ['peserta' => $this->ringkas($d)];

        if (! $d->user_id) {
            return $this->ok($dasar + ['data' => [], 'catatan' => 'Akun peserta belum ditautkan, belum ada tugas.']);
        }

        $baris = Tugas::where('user_id', $d->user_id)->orderByDesc('created_at')->get()
            ->map(fn ($t) => [
                'nama_file' => $t->nama_file_asli,
                'tipe' => $t->tipe_mime,
                'ukuran_kb' => (int) $t->ukuran_kb,
                'diunggah_pada' => $t->created_at?->toIso8601String(),
                'sudah_dilihat_admin' => $t->dilihat_admin_at !== null,
            ])->values();

        return $this->ok($dasar + ['data' => $baris]);
    }

    private function cari(string $nomor): DataMagang
    {
        $d = DataMagang::with(['departemen:id,nama'])->where('nis_nim', $nomor)->first();
        if (! $d) {
            $this->gagal('Peserta dengan NIS/NIM tersebut tidak ditemukan.', 404);
        }

        return $d;
    }

    /** Data yang boleh keluar lewat API. Sengaja TIDAK menyertakan: no KTP, alamat, tgl lahir, no WA, foto, berkas, GPS, device. */
    private function ringkas(DataMagang $d): array
    {
        return [
            'nis_nim' => $d->nis_nim,
            'nama' => $d->nama,
            'tipe_magang' => $d->tipe_magang,
            'jenis_kelamin' => $d->jenis_kelamin,
            'sekolah' => $d->asal_sekolah,
            'jurusan' => $d->jurusan,
            'departemen' => $d->departemen ? ['id' => $d->departemen->id, 'nama' => $d->departemen->nama] : null,
            'divisi' => $d->divisi,
            'tempat_prakerin' => $d->tempat_prakerin,
            'periode_magang' => $d->periode_magang,
            'tgl_mulai' => $d->tgl_mulai?->toDateString(),
            'tgl_selesai' => $d->tgl_selesai?->toDateString(),
            'status_magang' => $d->status_magang,
            'akun_terhubung' => $d->user_id !== null,
        ];
    }
}