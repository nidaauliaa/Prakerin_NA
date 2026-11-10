<?php

namespace App\Support;

use App\Models\HariLibur;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Satu-satunya tempat yang menentukan "hari kerja".
 *
 * Aturan:
 *  1. Tanggal yang masuk rentang hari libur (tabel hari_libur, atau config prakerin.libur) = BUKAN hari kerja.
 *  2. Tanggal yang terdaftar sebagai hari pengganti (tabel hari_pengganti) = hari kerja, walau Sabtu/Minggu.
 *  3. Selain itu: ikut config prakerin.hari_kerja (bawaan Senin-Jumat).
 *
 * Bila tabelnya belum dibuat (SQL belum dijalankan), aturan 3 + config libur tetap jalan; web tidak error.
 */
class KalenderKerja
{
    private static ?array $peta = null;

    private static function muat(): array
    {
        if (self::$peta !== null) {
            return self::$peta;
        }

        $libur = [];
        $pengganti = [];

        try {
            foreach (HariLibur::with('pengganti')->get() as $h) {
                $info = ['id' => $h->id, 'nama' => $h->nama, 'jenis' => $h->jenis, 'label' => $h->labelJenis()];

                for ($d = $h->tanggal_mulai->copy(); $d->lte($h->tanggal_selesai); $d->addDay()) {
                    $libur[$d->toDateString()] = $info;
                }
                foreach ($h->pengganti as $p) {
                    $pengganti[$p->tanggal->toDateString()] = $info;
                }
            }
        } catch (\Throwable $e) {
            // tabel belum ada
        }

        // Cadangan: tanggal libur lama di config/prakerin.php tetap dihormati.
        foreach ((array) config('prakerin.libur', []) as $t) {
            $libur[$t] ??= ['id' => null, 'nama' => 'Hari libur', 'jenis' => 'lainnya', 'label' => 'Libur'];
        }

        return self::$peta = ['libur' => $libur, 'pengganti' => $pengganti];
    }

    private static function kunci($t): string
    {
        return $t instanceof CarbonInterface ? $t->toDateString() : Carbon::parse($t)->toDateString();
    }

    public static function lupakan(): void
    {
        self::$peta = null;
    }

    /** @return array{id:?int,nama:string,jenis:string,label:string}|null */
    public static function infoLibur($tanggal): ?array
    {
        return self::muat()['libur'][self::kunci($tanggal)] ?? null;
    }

    /** Info libur yang "mengganti" tanggal ini (hanya ada untuk tanggal pengganti). */
    public static function infoPengganti($tanggal): ?array
    {
        return self::muat()['pengganti'][self::kunci($tanggal)] ?? null;
    }

    public static function hariKerja(CarbonInterface $tanggal): bool
    {
        $k = $tanggal->toDateString();
        $m = self::muat();

        if (isset($m['libur'][$k])) {
            return false;
        }
        if (isset($m['pengganti'][$k])) {
            return true;
        }

        return in_array($tanggal->dayOfWeekIso, (array) config('prakerin.hari_kerja'), true);
    }

    /** Keterangan singkat untuk tooltip kalender; null bila tanggal biasa. */
    public static function keterangan($tanggal): ?string
    {
        if ($i = self::infoLibur($tanggal)) {
            return $i['label'] . ' · ' . $i['nama'];
        }
        if ($i = self::infoPengganti($tanggal)) {
            return 'Hari kerja pengganti · ' . $i['nama'];
        }

        return null;
    }

    /**
     * Daftar catatan untuk satu bulan (dipakai di bawah kalender peserta):
     * [['tipe' => 'libur'|'pengganti', 'tanggal' => '20–24 Okt', 'teks' => 'Cuti Bersama · Idul Fitri'], ...]
     */
    public static function catatanBulan(CarbonInterface $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth()->toDateString();
        $akhir = $bulan->copy()->endOfMonth()->toDateString();
        $m = self::muat();
        $grup = [];

        foreach ($m['libur'] as $tgl => $info) {
            if ($tgl < $awal || $tgl > $akhir) {
                continue;
            }
            $grup['L' . ($info['id'] ?? $tgl)]['tipe'] = 'libur';
            $grup['L' . ($info['id'] ?? $tgl)]['info'] = $info;
            $grup['L' . ($info['id'] ?? $tgl)]['tgl'][] = $tgl;
        }
        foreach ($m['pengganti'] as $tgl => $info) {
            if ($tgl < $awal || $tgl > $akhir) {
                continue;
            }
            $grup['P' . $info['id']]['tipe'] = 'pengganti';
            $grup['P' . $info['id']]['info'] = $info;
            $grup['P' . $info['id']]['tgl'][] = $tgl;
        }

        $hasil = [];
        foreach ($grup as $g) {
            sort($g['tgl']);
            $fmt = fn ($t) => Carbon::parse($t)->locale('id')->translatedFormat('j M');
            $tanggal = $g['tipe'] === 'libur'
                ? (count($g['tgl']) > 1 ? $fmt($g['tgl'][0]) . '–' . $fmt(end($g['tgl'])) : $fmt($g['tgl'][0]))
                : implode(', ', array_map($fmt, $g['tgl']));

            $hasil[] = [
                'urut' => $g['tgl'][0],
                'tipe' => $g['tipe'],
                'tanggal' => $tanggal,
                'teks' => ($g['tipe'] === 'libur' ? $g['info']['label'] : 'Hari kerja pengganti') . ' · ' . $g['info']['nama'],
            ];
        }
        usort($hasil, fn ($a, $b) => strcmp($a['urut'], $b['urut']));

        return $hasil;
    }
}
