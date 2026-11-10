<?php

namespace App\Http\Controllers;

use App\Models\LogPerubahanStatus;
use App\Models\SesiPresensi;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class AttendanceController extends Controller
{
    // =========================================================
    // BANTUAN: CEK HARI KERJA
    // =========================================================

    /**
     * Mengecek apakah tanggal merupakan hari kerja.
     */
    public static function hariKerja(Carbon $tanggal): bool
    {
        // Senin-Jumat (config), dikurangi hari libur, ditambah hari kerja pengganti: diatur admin di menu Kalender Kerja.
        return \App\Support\KalenderKerja::hariKerja($tanggal);
    }

    // =========================================================
    // HALAMAN: KALENDER REKAP + SCAN + TUGAS
    // =========================================================

    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $bulan = Carbon::createFromFormat(
                '!Y-m',
                (string) $request->query(
                    'bulan',
                    now()->format('Y-m')
                )
            ) ?: now();
        } catch (\Throwable $e) {
            $bulan = now();
        }

        $bulan = $bulan->startOfMonth();

        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // Ambil semua sesi presensi bulan ini.
        $sesiBulanIni = SesiPresensi::with('laporanHarian')->where(
            'user_id',
            $user->id
        )
            ->whereBetween(
                'tanggal',
                [
                    $awal->toDateString(),
                    $akhir->toDateString(),
                ]
            )
            ->get()
            ->groupBy(function ($sesi) {
                return Carbon::parse($sesi->tanggal)
                    ->toDateString();
            });

        // Ambil perubahan status manual HRD.
        $logStatusBulanIni = LogPerubahanStatus::where(
            'user_id',
            $user->id
        )
            ->whereBetween(
                'tanggal',
                [
                    $awal->toDateString(),
                    $akhir->toDateString(),
                ]
            )
            ->get()
            ->keyBy(function ($log) {
                return Carbon::parse($log->tanggal)
                    ->toDateString();
            });

        // Hari pertama peserta benar-benar memiliki presensi.
        $tanggalPertama = SesiPresensi::where(
            'user_id',
            $user->id
        )->min('tanggal');

        $tanggalMulai = $tanggalPertama
            ? Carbon::parse($tanggalPertama)->startOfDay()
            : null;

        $hariDalamBulan = [];

        $hitung = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        // Persentase kehadiran bulan ini = hari hadir / SELURUH hari kerja di bulan itu (Senin-Jumat,
        // tanpa libur). Bulan baru otomatis mulai dari 0%. Kalau tgl_mulai/tgl_selesai magang diisi,
        // hari kerja di luar masa magang tidak dihitung.
        $persen = ['hadir' => 0, 'total' => 0];
        $magang = $user->dataMagang;
        $batasMulai = $magang?->tgl_mulai?->copy()->startOfDay();
        $batasSelesai = $magang?->tgl_selesai?->copy()->startOfDay();

        for (
            $tgl = $awal->copy();
            $tgl->lte($akhir);
            $tgl->addDay()
        ) {
            $key = $tgl->toDateString();

            $status = $this->tentukanStatusHari(
                $tgl->copy(),
                $sesiBulanIni->get($key, collect()),
                $logStatusBulanIni->get($key),
                $tanggalMulai
            );

            $hariDalamBulan[$key] = $status;

            if (array_key_exists($status['kode'], $hitung)) {
                $hitung[$status['kode']]++;
            }

            // Penyebut = semua hari kerja dalam masa magang (cuti tidak dihitung).
            // Pembilang = hari yang berstatus hadir. Weekend/libur tidak dihitung.
            if (self::hariKerja($tgl)) {
                $dalamMasaMagang = (! $batasMulai || $tgl->gte($batasMulai))
                    && (! $batasSelesai || $tgl->lte($batasSelesai));

                $dihitungDiPenyebut = $dalamMasaMagang && $status['kode'] !== 'cuti';

                if ($dihitungDiPenyebut) {
                    $persen['total']++;
                }

                if ($status['kode'] === 'hadir') {
                    $persen['hadir']++;

                    // Jaga-jaga: hadir di hari di luar masa magang tetap masuk penyebut agar tidak lebih dari 100%.
                    if (! $dihitungDiPenyebut) {
                        $persen['total']++;
                    }
                }
            }
        }

        $hariHadirPenuh = $persen['hadir'];
        $hariDihitung = $persen['total'];
        $persenHadir = $hariDihitung > 0
            ? (int) round($hariHadirPenuh / $hariDihitung * 100)
            : 0;

        // Presensi sore yang belum memiliki laporan harian.
        $laporanTertunda = SesiPresensi::where(
            'user_id',
            $user->id
        )
            ->where('sesi', 'sore')
            ->doesntHave('laporanHarian')
            ->orderByDesc('tanggal')
            ->limit(3)
            ->get();

        // Riwayat tugas.
        $riwayatTugas = Tugas::where(
            'user_id',
            $user->id
        )
            ->latest()
            ->limit(5)
            ->get();

        return view(
            'presensi.index',
            compact(
                'bulan',
                'hariDalamBulan',
                'hitung',
                'persenHadir',
                'hariHadirPenuh',
                'hariDihitung',
                'laporanTertunda',
                'riwayatTugas'
            )
        );
    }

    // =========================================================
    // MENENTUKAN STATUS HARI
    // =========================================================

    private function tentukanStatusHari(
        Carbon $tanggal,
        Collection $sesiHari,
        ?LogPerubahanStatus $logStatus,
        ?Carbon $tanggalMulai
    ): array {
        // Status manual HRD memiliki prioritas tertinggi.
        if ($logStatus) {
            $tandaManual = match ($logStatus->status_baru) {
                'izin' => 'I',
                'sakit' => 'S',
                'cuti' => 'C',
                'alpha' => '✕',
                default => '',
            };

            return [
                'kode' => $logStatus->status_baru,
                'tanda' => $tandaManual,
            ];
        }

        // Weekend / hari libur bukan alpha.
        if (
            !self::hariKerja($tanggal)
            && $sesiHari->isEmpty()
        ) {
            return [
                'kode' => 'libur',
                'tanda' => '',
            ];
        }

        // Jika presensi ditolak HRD → alpha.
        if (
            $sesiHari->contains(
                'status_persetujuan',
                'ditolak'
            )
        ) {
            return [
                'kode' => 'alpha',
                'tanda' => '✕',
            ];
        }

        $adaPagi = $sesiHari->contains(
            'sesi',
            'pagi'
        );

        $adaSore = $sesiHari->contains(
            'sesi',
            'sore'
        );

        // Pagi + sore lengkap: baru dihitung HADIR setelah laporan/presensi sore di-ACC admin/HRD.
        if ($adaPagi && $adaSore) {
            $sore = $sesiHari->firstWhere('sesi', 'sore');

            $sudahAcc = $sore
                && (
                    $sore->status_persetujuan === 'disetujui'
                    || $sore->laporanHarian?->status_review === 'disetujui'
                );

            if ($sudahAcc) {
                return [
                    'kode' => 'hadir',
                    'tanda' => '✓',
                ];
            }

            return [
                'kode' => 'perlu_acc',
                'tanda' => '!',
            ];
        }

        // Hanya satu sesi.
        if ($adaPagi || $adaSore) {
            // Kalau HRD sudah menyetujui.
            if (
                $sesiHari->contains(
                    'status_persetujuan',
                    'disetujui'
                )
            ) {
                return [
                    'kode' => 'hadir',
                    'tanda' => '✓',
                ];
            }

            $jamSoreTutup = $tanggal
                ->copy()
                ->setTime(
                    (int) config(
                        'prakerin.jendela.sore.selesai'
                    ),
                    0
                );

            // Hari ini masih berjalan.
            if (
                $tanggal->isToday()
                && now()->lt($jamSoreTutup)
            ) {
                return [
                    'kode' => 'progres',
                    'tanda' => '…',
                ];
            }

            return [
                'kode' => 'perlu_acc',
                'tanda' => '!',
            ];
        }

        // Hari ini atau masa depan.
        if (
            $tanggal->isFuture()
            || $tanggal->isToday()
        ) {
            return [
                'kode' => 'kosong',
                'tanda' => '',
            ];
        }

        // Sebelum peserta mulai memiliki data presensi.
        if (
            $tanggalMulai === null
            || $tanggal->lt($tanggalMulai)
        ) {
            return [
                'kode' => 'kosong',
                'tanda' => '',
            ];
        }

        // Tidak ada presensi pada hari kerja yang sudah lewat.
        return [
            'kode' => 'alpha',
            'tanda' => '✕',
        ];
    }

    // =========================================================
    // HALAMAN SCAN
    // =========================================================

    public function scan()
    {
        if (!self::hariKerja(now())) {
            $libur = \App\Support\KalenderKerja::infoLibur(now());

            return redirect()
                ->route('presensi.index')
                ->with(
                    'info',
                    $libur
                        ? 'Hari ini libur (' . $libur['label'] . ' · ' . $libur['nama'] . '), presensi tidak dibuka.'
                        : 'Hari ini bukan hari kerja, presensi tidak dibuka.'
                );
        }

        // Peserta sudah tidak aktif magang (selesai/nonaktif) -> presensi ditutup.
        if (Auth::user()->magangTidakAktif()) {
            return redirect()
                ->route('presensi.index')
                ->with('info', 'Masa magangmu sudah tidak aktif. Presensi tidak dapat dilakukan.');
        }

        // Masa magang sudah lewat -> presensi ditutup.
        $berakhir = Auth::user()->masaMagangBerakhir();

        if ($berakhir) {
            return redirect()
                ->route('presensi.index')
                ->with(
                    'info',
                    'Masa magangmu sudah berakhir pada ' . $berakhir->translatedFormat('d F Y')
                        . '. Presensi tidak dapat dilakukan.'
                );
        }

        // Zona absen: peserta hanya boleh absen di SATU pos yang ditentukan admin.
        $zonaPeserta = Auth::user()->zona();

        if ($zonaPeserta === null && config('prakerin.zona_wajib', false)) {
            return redirect()
                ->route('presensi.index')
                ->with(
                    'info',
                    'Zona absenmu belum ditentukan oleh admin. Hubungi admin/HRD sebelum melakukan presensi.'
                );
        }

        return view(
            'presensi.scan',
            [
                'posisi' => $zonaPeserta ? [$zonaPeserta] : \App\Models\ZonaAbsen::daftar(),
                'jendela' => config('prakerin.jendela'),
            ]
        );
    }

    // =========================================================
    // API: VERIFIKASI KARTU ID / QR
    // =========================================================

    public function verifikasiUuid(Request $request)
    {
        $user = Auth::user();

        if ($user->magangTidakAktif()) {
            return response()->json([
                'valid' => false,
                'pesan' => 'Masa magangmu sudah tidak aktif. Presensi tidak dapat dilakukan.',
            ]);
        }

        if ($berakhir = $user->masaMagangBerakhir()) {
            return response()->json([
                'valid' => false,
                'pesan' => 'Masa magangmu sudah berakhir pada ' . $berakhir->translatedFormat('d F Y')
                    . '. Presensi tidak dapat dilakukan.',
            ]);
        }

        $uuid = trim(
            (string) $request->input('uuid')
        );

        if (blank($user->uuid_kartu)) {
            return response()->json([
                'valid' => false,
                'pesan' => 'Akunmu belum memiliki Kartu ID. Hubungi admin/HRD.',
            ]);
        }

        if (
            !hash_equals(
                (string) $user->uuid_kartu,
                $uuid
            )
        ) {
            return response()->json([
                'valid' => false,
                'pesan' => 'Kartu ID ini bukan milik akun yang sedang login.',
            ]);
        }

        // Sesi ini sudah tercatat hari ini -> jangan minta selfie lagi (sore: arahkan ke laporan bila belum diisi).
        $sesiDicek = $request->input('sesi');

        if (in_array($sesiDicek, ['pagi', 'sore'], true)) {
            $ada = SesiPresensi::with('laporanHarian')
                ->where([
                    'user_id' => $user->id,
                    'tanggal' => now()->toDateString(),
                    'sesi' => $sesiDicek,
                ])
                ->first();

            if ($ada) {
                $jawab = [
                    'valid' => true,
                    'sudah' => true,
                    'nama' => $user->name,
                    'pesan' => 'Presensi sesi ' . $sesiDicek . ' hari ini sudah tercatat.',
                ];

                if ($sesiDicek === 'sore' && ! $ada->laporanHarian) {
                    $jawab['pesan'] .= ' Laporan harianmu belum diisi, mengalihkan ke formulir laporan.';
                    $jawab['laporanUrl'] = route('laporan.form', $ada);
                }

                return response()->json($jawab);
            }
        }

        return response()->json([
            'valid' => true,
            'nama' => $user->name,
            'nomorInduk' => $user->nis_nim,
            'divisi' => optional(
                $user->departemen
            )->nama,
        ]);
    }

    // =========================================================
    // API: SIMPAN PRESENSI
    // =========================================================

    public function simpan(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'uuid' => ['required', 'string'],
            'sesi' => ['required', 'in:pagi,sore'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'akurasi' => ['required', 'numeric', 'min:0'],
            'deviceId' => ['nullable', 'string', 'max:100'],
            'gpsSampel' => ['nullable', 'array', 'max:15'],
            'kirimWaktu' => ['nullable', 'numeric'],
            'fotoBase64' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Data presensi tidak lengkap atau tidak valid.',
            ], 422);
        }

        $data = $validator->validated();

        if ($user->magangTidakAktif()) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Masa magangmu sudah tidak aktif. Presensi tidak dapat dilakukan.',
            ], 403);
        }

        if ($berakhir = $user->masaMagangBerakhir()) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Masa magangmu sudah berakhir pada ' . $berakhir->translatedFormat('d F Y')
                    . '. Presensi tidak dapat dilakukan.',
            ], 403);
        }

        $sekarang = now();

        // =====================================================
        // 1. CEK HARI KERJA
        // =====================================================

        if (!self::hariKerja($sekarang)) {
            $libur = \App\Support\KalenderKerja::infoLibur($sekarang);

            return response()->json([
                'ok' => false,
                'pesan' => $libur
                    ? 'Hari ini libur (' . $libur['label'] . ' · ' . $libur['nama'] . '), presensi tidak dibuka.'
                    : 'Hari ini bukan hari kerja.',
            ], 422);
        }

        // =====================================================
        // 2. CEK KARTU ID
        // =====================================================

        if (
            blank($user->uuid_kartu)
            || !hash_equals(
                (string) $user->uuid_kartu,
                trim($data['uuid'])
            )
        ) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Kartu ID ini bukan milik akun yang sedang login.',
            ], 403);
        }

        // =====================================================
        // 3. CEK AKURASI GPS
        // =====================================================

        if (
            (float) $data['akurasi']
            > (float) config(
                'prakerin.maks_akurasi_meter'
            )
        ) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Akurasi GPS kurang baik. Coba lagi di tempat yang lebih terbuka.',
            ], 422);
        }

        // Opsional: batasi presensi hanya dari jaringan tertentu (config prakerin.ip_presensi).
        $ipBoleh = array_filter((array) config('prakerin.ip_presensi', []));

        if ($ipBoleh && ! IpUtils::checkIp((string) $request->ip(), array_values($ipBoleh))) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Presensi hanya dapat dilakukan dari jaringan/Wi-Fi kantor.',
            ], 403);
        }

        // Anti lokasi palsu: sinyal kuat = blokir, sinyal lemah = presensi ditahan untuk ACC admin.
        $curiga = ['skor' => 0, 'alasan' => []];

        if (config('prakerin.anti_gps_palsu', true)) {
            $curiga = $this->cekGpsPalsu($user, $data, (array) ($data['gpsSampel'] ?? []));

            if ($curiga['skor'] >= 3) {
                $this->catatLokasiCuriga($user, $data, $curiga, 'diblokir', null);

                \Illuminate\Support\Facades\Log::warning('Presensi diblokir (indikasi lokasi palsu)', [
                    'user_id' => $user->id,
                    'alasan' => $curiga['alasan'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'akurasi' => $data['akurasi'],
                ]);

                return response()->json([
                    'ok' => false,
                    'pesan' => 'Lokasi terdeteksi tidak wajar (kemungkinan lokasi palsu). '
                        . 'Matikan aplikasi lokasi palsu/mock location, lalu coba lagi di lokasi sebenarnya.',
                ], 422);
            }
        }

        // Zona absen: peserta hanya boleh absen di SATU pos yang ditentukan admin.
        $zona = $user->zona();

        if ($zona === null && config('prakerin.zona_wajib', false)) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Zona absen Anda belum ditentukan oleh admin. Hubungi admin/HRD.',
            ], 403);
        }

        if ($zona !== null) {
            $terdekat = [
                'pos' => $zona,
                'jarak' => $this->hitungJarakMeter(
                    (float) $zona['lat'],
                    (float) $zona['lng'],
                    (float) $data['lat'],
                    (float) $data['lng']
                ),
            ];
        } else {
            // Peserta tanpa zona (zona_wajib = false): aturan lama, pos terdekat dari semua pos.
            $terdekat = $this->posTerdekat(
                (float) $data['lat'],
                (float) $data['lng']
            );
        }

        if (
            $terdekat['jarak']
            > $terdekat['pos']['radius']
        ) {
            return response()->json([
                'ok' => false,
                'pesan' => $zona !== null
                    ? 'Anda tidak berada di zona absen Anda (' . $zona['nama'] . '). Absen hanya bisa dilakukan di zona yang ditentukan.'
                    : 'Anda tidak berada di salah satu zona absen yang terdaftar.',
            ], 422);
        }

        // =====================================================
        // 4. CEK PRESENSI GANDA
        // =====================================================

        $sudahAda = SesiPresensi::with(
            'laporanHarian'
        )
            ->where([
                'user_id' => $user->id,
                'tanggal' => $sekarang->toDateString(),
                'sesi' => $data['sesi'],
            ])
            ->first();

        if ($sudahAda) {
            $respons = [
                'ok' => false,
                'pesan' => 'Presensi sesi '
                    . $data['sesi']
                    . ' hari ini sudah tercatat.',
            ];

            if (
                $data['sesi'] === 'sore'
                && !$sudahAda->laporanHarian
            ) {
                $respons['pesan'] .=
                    ' Laporan harianmu belum diisi, mengalihkan ke formulir laporan.';

                $respons['laporanUrl'] = route(
                    'laporan.form',
                    $sudahAda
                );
            }

            return response()->json(
                $respons,
                409
            );
        }

        // =====================================================
        // 5. CEK JAM SESI
        // =====================================================

        $jam = $sekarang->hour
            + ($sekarang->minute / 60);

        $jendela = config(
            'prakerin.jendela.' . $data['sesi']
        );

        $dalamJendela =
            $jam >= $jendela['mulai']
            && $jam < $jendela['selesai'];

        if (!$dalamJendela) {
            $namaSesi = $data['sesi'] === 'pagi'
                ? 'Presensi Pagi'
                : 'Presensi Sore';

            return response()->json([
                'ok' => false,
                'pesan' => $namaSesi
                    . ' hanya dapat dilakukan pukul '
                    . sprintf(
                        '%02d.00',
                        $jendela['mulai']
                    )
                    . ' - '
                    . sprintf(
                        '%02d.00',
                        $jendela['selesai']
                    )
                    . ' WIB.',
            ], 422);
        }

        // =====================================================
        // 6. SIMPAN DATA
        // =====================================================

        $pathFoto = null;

        try {
            $sesiPresensi = DB::transaction(
                function () use (
                    $user,
                    $data,
                    $sekarang,
                    $terdekat,
                    $dalamJendela,
                    $curiga,
                    &$pathFoto
                ) {
                    // Selfie dokumentasi wajib untuk presensi pagi DAN sore.
                    if ($data['sesi'] === 'pagi' || $data['sesi'] === 'sore') {
                        $pathFoto = $this->simpanFotoSelfie(
                            (string) $data['fotoBase64'],
                            $user->id
                        );

                        if ($pathFoto === null) {
                            throw new \RuntimeException(
                                'FOTO_TIDAK_VALID'
                            );
                        }
                    }

                    return SesiPresensi::create([
                        'user_id' => $user->id,

                        'tanggal' => $sekarang->toDateString(),

                        'sesi' => $data['sesi'],

                        'waktu_absen' => $sekarang,

                        // Nama lokasi berasal dari server.
                        'lokasi' => $terdekat['pos']['nama'],

                        // Koordinat HP.
                        'lat' => round(
                            (float) $data['lat'],
                            7
                        ),

                        'lng' => round(
                            (float) $data['lng'],
                            7
                        ),

                        'jarak_meter' => round(
                            $terdekat['jarak'],
                            2
                        ),

                        'akurasi_gps' => round(
                            (float) $data['akurasi'],
                            2
                        ),

                        'foto_url' => $pathFoto,

                        'device_id' =>
                            $data['deviceId'] ?? null,

                        'status' => 'hadir',

                        // Lokasi mencurigakan (sinyal lemah) -> ditahan untuk ACC admin.
                        'status_persetujuan' =>
                            ($dalamJendela && empty($curiga['alasan']))
                                ? 'auto'
                                : 'menunggu',

                        'catatan_approval' => empty($curiga['alasan'])
                            ? null
                            : 'Otomatis: lokasi mencurigakan (' . implode('; ', $curiga['alasan']) . ')',
                    ]);
                }
            );
        } catch (\RuntimeException $e) {
            if (
                $e->getMessage()
                === 'FOTO_TIDAK_VALID'
            ) {
                return response()->json([
                    'ok' => false,
                    'pesan' =>
                        'Foto selfie tidak valid '
                        . '(harus JPEG, maks. '
                        . config(
                            'prakerin.maks_foto_kb'
                        )
                        . ' KB).',
                ], 422);
            }

            throw $e;
        } catch (QueryException $e) {
            if ($pathFoto) {
                Storage::disk('local')
                    ->delete($pathFoto);
            }

            // 23000 = duplicate / unique violation.
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'ok' => false,
                    'pesan' =>
                        'Presensi sesi ini sudah tercatat.',
                ], 409);
            }

            throw $e;
        }

        // Sinyal lemah: presensi sudah tersimpan tetapi ditahan untuk ACC admin -> catat di log lokasi curiga.
        if (! empty($curiga['alasan'])) {
            $this->catatLokasiCuriga($user, $data, $curiga, 'ditahan', $sesiPresensi->id);
        }

        // =====================================================
        // 7. RESPONSE BERHASIL
        // =====================================================

        return response()->json([
            'ok' => true,

            'sesiPresensiId' =>
                $sesiPresensi->id,

            'nama' =>
                $user->name,

            'statusPersetujuan' =>
                $sesiPresensi->status_persetujuan,

            'fotoUrl' =>
                $pathFoto
                    ? route(
                        'presensi.foto',
                        $sesiPresensi
                    )
                    : null,

            'fotoProfilUrl' =>
                $user->fotoSayaUrl(),
        ]);
    }

    // =========================================================
    // FOTO SELFIE
    // =========================================================

    public function foto(SesiPresensi $sesiPresensi)
    {
        $user = Auth::user();

        abort_unless(
            $sesiPresensi->user_id === $user->id ||
            in_array(
                $user->peran,
                config('prakerin.peran_peninjau'),
                true
            ),
            403
        );

        abort_if(
            blank($sesiPresensi->foto_url) ||
            ! Storage::disk('local')->exists($sesiPresensi->foto_url),
            404
        );

        return response()->file(
            Storage::disk('local')->path($sesiPresensi->foto_url)
        );
    }

    // Foto pas milik peserta yang sedang login. Disajikan lewat PHP (bukan /storage) supaya tidak
    // rusak kalau APP_URL / subfolder situs berbeda (QC-016, QC-043).
    public function fotoSaya()
    {
        $path = Auth::user()?->dataMagang?->foto_pas;
        $disk = Storage::disk('public');

        abort_if(blank($path) || ! $disk->exists($path), 404);

        return response()->file($disk->path($path));
    }

    // =========================================================
    // BANTUAN: LOKASI TERDEKAT
    // =========================================================

    private function posTerdekat(
        float $lat,
        float $lng
    ): array {
        $terdekat = null;

        foreach (
            \App\Models\ZonaAbsen::daftar() as $pos
        ) {
            $jarak = $this->hitungJarakMeter(
                $pos['lat'],
                $pos['lng'],
                $lat,
                $lng
            );

            if (
                $terdekat === null
                || $jarak < $terdekat['jarak']
            ) {
                $terdekat = [
                    'pos' => $pos,
                    'jarak' => $jarak,
                ];
            }
        }

        return $terdekat;
    }

    // =========================================================
    // BANTUAN: HITUNG JARAK HAVERSINE
    // =========================================================

    /**
     * Deteksi lokasi palsu (best effort). Browser tidak bisa melihat apakah GPS "mock", jadi yang
     * diperiksa adalah pola yang jarang terjadi pada GPS asli. Hasil: skor + alasan.
     *   skor >= 3  -> presensi DIBLOKIR (sinyal kuat)
     *   skor 1..2  -> presensi diterima tapi "Menunggu ACC" admin (sinyal lemah)
     * Catatan: klien yang memalsukan seluruh request tetap bisa lolos dari pemeriksaan sampel;
     * pemeriksaan no. 5 dan 6 berjalan di server dan tidak bergantung pada klien.
     */
    private function cekGpsPalsu(\App\Models\User $user, array $data, array $sampel): array
    {
        $skor = 0;
        $alasan = [];
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $akurasi = (float) $data['akurasi'];

        // Rapikan sampel dari klien (maks 12 terakhir).
        $s = [];
        foreach (array_slice($sampel, -12) as $x) {
            if (! is_array($x) || ! isset($x['lat'], $x['lng']) || ! is_numeric($x['lat']) || ! is_numeric($x['lng'])) {
                continue;
            }
            $s[] = [
                'lat' => (float) $x['lat'],
                'lng' => (float) $x['lng'],
                'a' => isset($x['a']) && is_numeric($x['a']) ? (float) $x['a'] : null,
                's' => isset($x['s']) && is_numeric($x['s']) ? (float) $x['s'] : null,
                't' => isset($x['t']) && is_numeric($x['t']) ? (float) $x['t'] : null,
            ];
        }

        // 1. Akurasi terlalu sempurna: GPS HP asli di browser umumnya 3-20 m. Aplikasi fake GPS sering
        //    melaporkan akurasi tetap 1 m. Batasnya diatur di config prakerin.akurasi_min_meter.
        $akurasiMin = (float) config('prakerin.akurasi_min_meter', 2.0);
        if ($akurasi < $akurasiMin) {
            $skor += 3;
            $alasan[] = 'akurasi GPS tidak wajar (' . round($akurasi, 2) . ' m, terlalu sempurna untuk GPS HP)';
        }

        // 2. Kecepatan mustahil (> 55 m/s = 198 km/j).
        foreach ($s as $x) {
            if ($x['s'] !== null && $x['s'] > 55) {
                $skor += 3;
                $alasan[] = 'kecepatan tidak wajar (' . round($x['s']) . ' m/s)';
                break;
            }
        }

        // 3. Lokasi melompat jauh antar sampel yang akurasinya bagus (hanya beberapa detik).
        $yakin = array_values(array_filter($s, fn ($x) => $x['a'] !== null && $x['a'] <= 30));
        $lompat = 0.0;
        for ($i = 0; $i < count($yakin); $i++) {
            for ($j = $i + 1; $j < count($yakin); $j++) {
                $lompat = max($lompat, $this->hitungJarakMeter(
                    $yakin[$i]['lat'],
                    $yakin[$i]['lng'],
                    $yakin[$j]['lat'],
                    $yakin[$j]['lng']
                ));
            }
        }
        if ($lompat > 150) {
            $skor += 3;
            $alasan[] = 'lokasi melompat ' . round($lompat) . ' m dalam beberapa detik';
        }

        // 4. Tidak ada variasi sama sekali (koordinat dan akurasi identik di semua sampel).
        if (count($s) >= 5) {
            $unik = count(array_unique(array_map(fn ($x) => $x['lat'] . ',' . $x['lng'] . ',' . $x['a'], $s)));
            $waktu = array_filter(array_column($s, 't'), fn ($t) => $t !== null);
            $rentang = $waktu ? (max($waktu) - min($waktu)) : 0;
            if ($unik === 1 && $rentang >= 3000) {
                $skor += 2;
                $alasan[] = 'koordinat sama persis pada semua sampel';
            }
        }

        // 5. Perpindahan mustahil dari presensi sebelumnya (server, tidak bisa dipalsukan klien).
        $prev = SesiPresensi::where('user_id', $user->id)
            ->whereNotNull('lat')->whereNotNull('lng')->whereNotNull('waktu_absen')
            ->where('waktu_absen', '>=', now()->subDay())
            ->latest('waktu_absen')->first();
        if ($prev) {
            $jarak = $this->hitungJarakMeter((float) $prev->lat, (float) $prev->lng, $lat, $lng);
            $detik = max(1, abs(now()->timestamp - $prev->waktu_absen->timestamp));
            if ($jarak > 1000 && ($jarak / $detik) > 40) {
                $skor += 3;
                $alasan[] = 'berpindah ' . round($jarak / 1000, 1) . ' km dalam ' . round($detik / 60) . ' menit dari presensi sebelumnya';
            }
        }

        // 6. Koordinat persis sama dengan peserta LAIN dalam 30 menit terakhir (berbagi lokasi palsu).
        $kembar = SesiPresensi::where('user_id', '!=', $user->id)
            ->where('waktu_absen', '>=', now()->subMinutes(30))
            ->whereBetween('lat', [$lat - 0.00000015, $lat + 0.00000015])
            ->whereBetween('lng', [$lng - 0.00000015, $lng + 0.00000015])
            ->exists();
        if ($kembar) {
            $skor += 2;
            $alasan[] = 'koordinat persis sama dengan peserta lain';
        }

        // 7. Pergeseran (jitter) GPS nyaris nol padahal koordinat tidak identik: terlalu halus untuk GPS asli.
        $jitterMin = (float) config('prakerin.jitter_min_meter', 0.05);
        $jitter = $this->hitungJitterMeter($s);
        if ($jitterMin > 0 && $jitter !== null && $jitter > 0 && $jitter < $jitterMin) {
            $skor += 1;
            $alasan[] = 'pergeseran GPS nyaris nol (' . round($jitter, 3) . ' m), tidak alami';
        }

        // 8. Data lokasi basi: sampel terakhir jauh lebih tua dari saat presensi dikirim (lokasi hasil cache/replay).
        $batasBasi = (float) config('prakerin.lokasi_basi_detik', 180);
        $kirim = isset($data['kirimWaktu']) && is_numeric($data['kirimWaktu']) ? (float) $data['kirimWaktu'] : null;
        $waktuSampel = array_filter(array_column($s, 't'), fn ($t) => $t !== null);
        if ($batasBasi > 0 && $kirim !== null && $waktuSampel) {
            $umur = ($kirim - max($waktuSampel)) / 1000;
            if ($umur > $batasBasi) {
                $skor += 1;
                $alasan[] = 'data lokasi sudah basi (' . round($umur) . ' detik sebelum presensi)';
            }
        }

        // 9. Titik yang sama persis dengan presensi peserta ini di hari lain (server, tidak bisa dipalsukan klien).
        if (config('prakerin.koordinat_ulang_cek', true)) {
            $lat7 = round($lat, 7);
            $lng7 = round($lng, 7);
            $hariUlang = SesiPresensi::where('user_id', $user->id)
                ->where('tanggal', '<>', now()->toDateString())
                ->whereBetween('lat', [$lat7 - 0.0000002, $lat7 + 0.0000002])
                ->whereBetween('lng', [$lng7 - 0.0000002, $lng7 + 0.0000002])
                ->distinct()
                ->count('tanggal');

            if ($hariUlang >= 2) {
                $skor += 3;
                $alasan[] = 'koordinat persis sama dengan presensi di ' . $hariUlang . ' hari lain (GPS asli tidak mengulang titik sampai sedetail ini)';
            } elseif ($hariUlang === 1) {
                $skor += 2;
                $alasan[] = 'koordinat persis sama dengan presensi di hari lain';
            }
        }

        // 10. Posisi terlalu persis di titik pusat zona (pola umum aplikasi fake GPS).
        $pusatMin = (float) config('prakerin.pusat_zona_meter', 1.5);
        if ($pusatMin > 0) {
            $jarakPusat = null;
            try {
                $zonaUser = $user->zona();
                if ($zonaUser !== null) {
                    $jarakPusat = $this->hitungJarakMeter((float) $zonaUser['lat'], (float) $zonaUser['lng'], $lat, $lng);
                } else {
                    $tp = $this->posTerdekat($lat, $lng);
                    $jarakPusat = is_array($tp) && isset($tp['jarak']) ? (float) $tp['jarak'] : null;
                }
            } catch (\Throwable $e) {
                $jarakPusat = null;
            }

            if ($jarakPusat !== null && $jarakPusat < $pusatMin) {
                $skor += 2;
                $alasan[] = 'posisi tepat di titik pusat zona (' . round($jarakPusat, 2) . ' m), pola lokasi palsu';
            }
        }

        // 11. Perangkat yang sama dipakai akun peserta lain dalam 24 jam (dugaan titip absen). Hanya menahan, tidak memblokir.
        $deviceId = $data['deviceId'] ?? null;
        if ($deviceId && config('prakerin.perangkat_bersama_cek', true)) {
            $akunLain = SesiPresensi::where('device_id', $deviceId)
                ->where('user_id', '!=', $user->id)
                ->where('waktu_absen', '>=', now()->subDay())
                ->distinct()
                ->pluck('user_id')
                ->count();

            if ($akunLain > 0) {
                $skor += 2;
                $alasan[] = 'perangkat ini juga dipakai ' . $akunLain . ' akun peserta lain dalam 24 jam (dugaan titip absen)';
            }
        }

        return ['skor' => $skor, 'alasan' => $alasan];
    }

    /**
     * Simpangan baku posisi (meter) dari sampel GPS yang akurasinya baik.
     * Mengembalikan null bila sampel belum cukup (kurang dari 6 titik berbeda atau rentang kurang dari 4 detik).
     */
    private function hitungJitterMeter(array $sampel): ?float
    {
        $pakai = array_values(array_filter(
            $sampel,
            fn ($x) => $x['a'] !== null && $x['a'] <= 30 && $x['t'] !== null
        ));

        if (count($pakai) < 6 || count(array_unique(array_column($pakai, 't'))) < 6) {
            return null;
        }

        $waktu = array_column($pakai, 't');
        if ((max($waktu) - min($waktu)) < 4000) {
            return null;
        }

        $n = count($pakai);
        $mLat = array_sum(array_column($pakai, 'lat')) / $n;
        $mLng = array_sum(array_column($pakai, 'lng')) / $n;
        $kosLat = cos(deg2rad($mLat));

        $jumlah = 0.0;
        foreach ($pakai as $x) {
            $dy = ($x['lat'] - $mLat) * 111320;
            $dx = ($x['lng'] - $mLng) * 111320 * $kosLat;
            $jumlah += $dx * $dx + $dy * $dy;
        }

        return sqrt($jumlah / $n);
    }

    /**
     * Catat percobaan/presensi berindikasi lokasi palsu agar bisa ditinjau admin (menu "Lokasi Curiga").
     * Dibungkus try/catch: kegagalan mencatat tidak boleh mengganggu presensi.
     */
    private function catatLokasiCuriga(
        \App\Models\User $user,
        array $data,
        array $curiga,
        string $tindakan,
        ?int $sesiPresensiId
    ): void {
        try {
            \App\Models\LogLokasiCuriga::create([
                'user_id' => $user->id,
                'sesi_presensi_id' => $sesiPresensiId,
                'sesi' => $data['sesi'] ?? null,
                'tindakan' => $tindakan,
                'skor' => (int) ($curiga['skor'] ?? 0),
                'alasan' => array_values($curiga['alasan'] ?? []),
                'lat' => isset($data['lat']) ? round((float) $data['lat'], 7) : null,
                'lng' => isset($data['lng']) ? round((float) $data['lng'], 7) : null,
                'akurasi' => isset($data['akurasi']) ? round((float) $data['akurasi'], 2) : null,
                'device_id' => $data['deviceId'] ?? null,
                'ip' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mencatat log lokasi curiga', ['error' => $e->getMessage()]);
        }
    }

    private function hitungJarakMeter(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $r = 6371000;

        $dLat = deg2rad(
            $lat2 - $lat1
        );

        $dLng = deg2rad(
            $lng2 - $lng1
        );

        $a =
            sin($dLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        return $r
            * 2
            * atan2(
                sqrt($a),
                sqrt(1 - $a)
            );
    }

    // =========================================================
    // BANTUAN: SIMPAN FOTO SELFIE
    // =========================================================

    private function simpanFotoSelfie(
        string $fotoBase64,
        int $userId
    ): ?string {
        /*
         * Format yang diharapkan:
         *
         * data:image/jpeg;base64,XXXXXX
         */

        $jumlahAwalan = 0;

        $mentah = preg_replace(
            '/^data:image\/jpeg;base64,/',
            '',
            $fotoBase64,
            1,
            $jumlahAwalan
        );

        if ($jumlahAwalan !== 1 || $mentah === null) {
            return null;
        }

        // Decode Base64.
        $biner = base64_decode(
            $mentah,
            true
        );

        if (
            $biner === false
            || $biner === ''
            || strlen($biner)
                > (
                    (int) config(
                        'prakerin.maks_foto_kb'
                    ) * 1024
                )
        ) {
            return null;
        }

        // Pastikan benar-benar JPEG.
        $info = @getimagesizefromstring(
            $biner
        );

        if (
            !$info
            || ($info['mime'] ?? null)
                !== 'image/jpeg'
        ) {
            return null;
        }

        // Nama file unik.
        $path =
            'selfie/'
            . $userId
            . '/'
            . Str::uuid()
            . '.jpg';

        Storage::disk('local')->put(
            $path,
            $biner
        );

        return $path;
    }
}