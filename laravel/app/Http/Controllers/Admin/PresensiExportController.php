<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\LogPerubahanStatus;
use App\Models\SesiPresensi;
use App\Models\User;
use App\Support\KalenderKerja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export "Rekap Absensi Prakerin": satu file = satu bulan, satu baris per peserta,
 * satu kolom per tanggal (01-31). Isi sel hanya 1 (masuk) atau 0 (tidak masuk).
 */
class PresensiExportController extends Controller
{
    // Kolom tetap: A TAHUN, B NO BULAN, C BULAN, D NAMA, E SEKOLAH/UNIVERSITAS, F DEPARTEMEN.
    private const KOLOM_TGL_PERTAMA = 7;   // G  = tanggal 01
    private const BARIS_DATA_PERTAMA = 4;

    /** Halaman "Export Rekap Presensi" (tampilan seperti halaman Import). */
    public function form()
    {
        return view('admin.presensi.export', [
            'departemen' => Departemen::orderBy('nama')->pluck('nama'),
            'bulanDefault' => today()->format('Y-m'),
        ]);
    }

    public function download(Request $r)
    {
        $v = $r->validate([
            'bulan' => 'required|date_format:Y-m',
            'departemen' => 'nullable|string',
        ]);

        // Hosting gratis sering membatasi memori/waktu; sheet ini banyak sel berwarna.
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        $awal = Carbon::createFromFormat('!Y-m', $v['bulan'])->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
        $jumlahHari = $awal->daysInMonth;
        $hariIni = today()->toDateString();

        // Peserta yang masa magangnya beririsan dengan bulan terpilih (tanggal mulai/selesai kosong = tidak dibatasi).
        // Peserta tanpa data magang hanya ikut bila punya presensi di bulan itu.
        $rentang = [$awal->toDateString(), $akhir->toDateString()];
        $users = User::with(['departemen', 'dataMagang'])
            ->where('peran', 'peserta')
            ->where(function ($q) use ($rentang) {
                $q->whereHas('dataMagang', fn ($d) => $d
                        ->where(fn ($w) => $w->whereNull('tgl_mulai')->orWhere('tgl_mulai', '<=', $rentang[1]))
                        ->where(fn ($w) => $w->whereNull('tgl_selesai')->orWhere('tgl_selesai', '>=', $rentang[0])))
                  ->orWhere(fn ($w) => $w->whereDoesntHave('dataMagang')
                        ->whereHas('sesiPresensi', fn ($s) => $s->whereBetween('tanggal', $rentang)));
            })
            ->when($v['departemen'] ?? null, fn ($q, $dep) => $q->whereHas('departemen', fn ($d) => $d->where('nama', $dep)))
            ->get()
            ->sortBy(fn (User $u) => [
                mb_strtolower((string) ($u->dataMagang?->asal_sekolah ?? $u->asal_sekolah)),
                mb_strtolower($u->name),
            ])->values();

        $ids = $users->pluck('id');

        $sesi = SesiPresensi::with('laporanHarian')
            ->whereBetween('tanggal', $rentang)
            ->whereIn('user_id', $ids)
            ->get()
            ->groupBy(fn ($s) => $s->user_id . '|' . Carbon::parse($s->tanggal)->toDateString());

        // Urut id naik: log yang lebih baru menimpa yang lama (sama seperti rekap di halaman Presensi).
        $log = [];
        LogPerubahanStatus::whereBetween('tanggal', $rentang)
            ->whereIn('user_id', $ids)
            ->orderBy('id')
            ->get(['id', 'user_id', 'tanggal', 'status_baru'])
            ->each(function ($l) use (&$log) {
                $log[$l->user_id . '|' . Carbon::parse($l->tanggal)->toDateString()] = $l->status_baru;
            });

        // Tanggal libur (Sabtu/Minggu, libur nasional, cuti bersama) = bukan hari kerja.
        $hariKerja = [];
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $hariKerja[$d] = KalenderKerja::hariKerja($awal->copy()->day($d));
        }

        // Hari kerja SAMA untuk semua peserta: semua hari kerja di bulan itu (bulan berjalan: sampai hari ini).
        $hkBulan = 0;
        for ($d = 1; $d <= $jumlahHari; $d++) {
            if ($hariKerja[$d] && $awal->copy()->day($d)->toDateString() <= $hariIni) {
                $hkBulan++;
            }
        }

        // ---------- Susun sheet ----------
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Rekap Absensi');

        $kTgl = fn (int $d) => Coordinate::stringFromColumnIndex(self::KOLOM_TGL_PERTAMA + $d - 1);
        $kAwalTgl = $kTgl(1);                                                     // G
        $kAkhirTgl = $kTgl(31);                                                   // AK
        $kJml = Coordinate::stringFromColumnIndex(self::KOLOM_TGL_PERTAMA + 31);     // AL
        $kPersen = Coordinate::stringFromColumnIndex(self::KOLOM_TGL_PERTAMA + 32);  // AM
        $kHk = Coordinate::stringFromColumnIndex(self::KOLOM_TGL_PERTAMA + 33);      // AN

        // Judul
        $sh->setCellValue('A1', 'REKAP ABSENSI PRAKERIN');
        $sh->mergeCells("A1:{$kHk}1");
        $sh->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 28],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sh->getRowDimension(1)->setRowHeight(48);

        // Baris 2: kelompok judul kolom
        $sh->setCellValue('A2', 'IDENTITAS');
        $sh->mergeCells('A2:F2');
        $sh->setCellValue('G2', 'Tanggal');
        $sh->mergeCells("G2:{$kAkhirTgl}2");
        $sh->setCellValue("{$kJml}2", "Jumlah\nKehadiran");
        $sh->setCellValue("{$kPersen}2", "Persentase\nKehadiran");
        $sh->setCellValue("{$kHk}2", "Hari\nKerja");
        $sh->getRowDimension(2)->setRowHeight(30);

        // Baris 3: judul kolom
        $sh->fromArray(['TAHUN', "no\nbulan", 'BULAN', 'NAMA', 'SEKOLAH / UNIVERSITAS', 'DEPARTEMEN'], null, 'A3');
        for ($d = 1; $d <= 31; $d++) {
            $sh->setCellValue($kTgl($d) . '3', str_pad((string) $d, 2, '0', STR_PAD_LEFT));
        }
        $sh->setCellValue("{$kJml}3", 'JML');
        $sh->setCellValue("{$kPersen}3", '%');
        $sh->setCellValue("{$kHk}3", 'HK');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sh->getStyle("A2:{$kHk}3")->applyFromArray($headerStyle);
        $sh->getRowDimension(3)->setRowHeight(28);

        // ---------- Baris peserta ----------
        $bulanNama = mb_strtoupper($awal->translatedFormat('F'));
        $baris = self::BARIS_DATA_PERTAMA;

        foreach ($users as $u) {
            $dm = $u->dataMagang;
            $mulai = $dm?->tgl_mulai ? Carbon::parse($dm->tgl_mulai)->toDateString() : null;
            $selesai = $dm?->tgl_selesai ? Carbon::parse($dm->tgl_selesai)->toDateString() : null;

            $sh->fromArray([[
                (int) $awal->format('Y'),
                $awal->format('m'),
                $bulanNama,
                mb_strtoupper($u->name),
                mb_strtoupper((string) ($dm?->asal_sekolah ?? $u->asal_sekolah)),
                mb_strtoupper((string) optional($u->departemen)->nama),
            ]], null, "A{$baris}");
            $sh->getCell("B{$baris}")->setValueExplicit($awal->format('m'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            for ($d = 1; $d <= 31; $d++) {
                $sel = $kTgl($d) . $baris;
                $style = $sh->getStyle($sel);

                // Tanggal yang tidak ada di bulan ini (mis. 31 September) atau hari libur: arsir.
                if ($d > $jumlahHari || ! $hariKerja[$d]) {
                    $style->getFill()->setFillType(Fill::FILL_PATTERN_LIGHTUP);
                    $style->getFill()->getStartColor()->setRGB('000000');
                    $style->getFill()->getEndColor()->setRGB('FFFFFF');
                    continue;
                }

                $tgl = $awal->copy()->day($d)->toDateString();

                // Di luar masa magang peserta: kosong putih.
                if (($mulai && $tgl < $mulai) || ($selesai && $tgl > $selesai)) {
                    continue;
                }

                // Hari kerja dalam masa magang: hijau. Tanggal yang belum terjadi dibiarkan kosong.
                $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('A9D08E');
                if ($tgl > $hariIni) {
                    continue;
                }

                $key = $u->id . '|' . $tgl;
                $masuk = $this->masuk($log[$key] ?? null, $sesi->get($key, collect()));
                $sh->setCellValue($sel, $masuk ? 1 : 0);
                if (! $masuk) {
                    $style->getFill()->getStartColor()->setRGB('FFFF00');   // kuning = tidak masuk
                }
            }

            $sh->setCellValue("{$kJml}{$baris}", "=SUM({$kAwalTgl}{$baris}:{$kAkhirTgl}{$baris})");
            $sh->setCellValue("{$kHk}{$baris}", $hkBulan);
            $sh->setCellValue("{$kPersen}{$baris}", "=IF({$kHk}{$baris}>0,{$kJml}{$baris}/{$kHk}{$baris},0)");
            $baris++;
        }

        $akhirBaris = max($baris - 1, self::BARIS_DATA_PERTAMA);

        // Gaya isi tabel
        $sh->getStyle("A" . self::BARIS_DATA_PERTAMA . ":{$kHk}{$akhirBaris}")->applyFromArray([
            'font' => ['size' => 9],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sh->getStyle("G" . self::BARIS_DATA_PERTAMA . ":{$kHk}{$akhirBaris}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle("A" . self::BARIS_DATA_PERTAMA . ":C{$akhirBaris}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle("{$kPersen}" . self::BARIS_DATA_PERTAMA . ":{$kPersen}{$akhirBaris}")
            ->getNumberFormat()->setFormatCode('0%');

        // Lebar kolom
        foreach (['A' => 7, 'B' => 6, 'C' => 12, 'D' => 30, 'E' => 42, 'F' => 22] as $k => $w) {
            $sh->getColumnDimension($k)->setWidth($w);
        }
        for ($d = 1; $d <= 31; $d++) {
            $sh->getColumnDimension($kTgl($d))->setWidth(3.6);
        }
        $sh->getColumnDimension($kJml)->setWidth(11);
        $sh->getColumnDimension($kPersen)->setWidth(12);
        $sh->getColumnDimension($kHk)->setWidth(8);

        $sh->freezePane('G' . self::BARIS_DATA_PERTAMA);
        // Pengaturan cetak hanya pemanis: kalau method-nya beda di versi PhpSpreadsheet, jangan sampai export gagal.
        try {
            $sh->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sh->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            $sh->getPageSetup()->setFitToPage(true);
        } catch (\Throwable $e) {
            report($e);
        }

        $nama = 'rekap-absensi-prakerin-' . $awal->format('Y-m')
            . (($v['departemen'] ?? null) ? '-' . Str::slug($v['departemen']) : '') . '.xlsx';

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * true = masuk (1), false = tidak masuk (0). Izin, sakit, cuti, alpha, ditolak, dan tidak ada data semuanya 0.
     * Aturannya sama dengan rekap bulanan di halaman Presensi, supaya persentasenya cocok:
     * koreksi admin (log) menang; kalau tidak ada, lihat sesi presensi hari itu.
     */
    private function masuk(?string $log, Collection $sesi): bool
    {
        if ($log) {
            return $log === 'hadir';
        }
        if ($sesi->isEmpty()) {
            return false;
        }
        if ($sesi->contains(fn ($s) => $s->status === 'alpha' || $s->status_persetujuan === 'ditolak')) {
            return false;
        }
        if ($sesi->contains(fn ($s) => $s->status !== 'hadir')) {
            return false;
        }

        // Pagi + sore baru dihitung masuk setelah sore di-ACC (opsional, lihat config prakerin.rekap_wajib_acc).
        if (config('prakerin.rekap_wajib_acc', true)) {
            $sore = $sesi->firstWhere('sesi', 'sore');
            if ($sore && $sesi->contains('sesi', 'pagi')
                && ! ($sore->status_persetujuan === 'disetujui' || $sore->laporanHarian?->status_review === 'disetujui')) {
                return false;
            }
        }

        return true;
    }
}