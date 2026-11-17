<?php

namespace App\Services;

use App\Models\DataMagang;
use App\Models\Penilaian;
use App\Models\SuratKeterangan;

/**
 * Memasukkan hasil penilaian ke Surat Keterangan peserta.
 *  - nilai akhir surat = rata-rata nilai_akhir semua penilaian peserta tsb (biasanya hanya satu form)
 *  - surat dibuat otomatis berstatus DRAFT; nomor & tanggal surat diisi HRD, lalu diterbitkan
 *  - surat yang sudah TERBIT tidak ikut berubah sendiri; HRD menekan "Perbarui nilai" bila memang perlu
 */
class SinkronSuratKeterangan
{
    public function jalankan(DataMagang $dm, bool $paksa = false): ?SuratKeterangan
    {
        $penilaian = $dm->penilaian()->get(['nilai_akhir']);
        $surat     = SuratKeterangan::where('data_magang_id', $dm->id)->first();

        if ($penilaian->isEmpty()) {
            return $surat;   // belum ada penilaian: jangan membuat surat kosong
        }

        if ($surat && $surat->terbit() && ! $paksa) {
            return $surat;
        }

        $rata = round($penilaian->avg('nilai_akhir'), 2);

        $isi = [
            'nilai_akhir'      => $rata,
            'predikat'         => Penilaian::predikat($rata),
            'jumlah_penilaian' => $penilaian->count(),
        ];

        if (! $surat) {
            $surat = new SuratKeterangan(['data_magang_id' => $dm->id, 'status' => 'draft']);
            // Usulan awal dari data peserta; HRD masih bisa mengubahnya.
            $isi += [
                'penempatan'  => $dm->divisi ?: $dm->departemen?->nama,
                'tgl_mulai'   => $dm->tgl_mulai,
                'tgl_selesai' => $dm->tgl_selesai,
            ];
        }

        $surat->fill($isi)->save();

        return $surat;
    }
}
