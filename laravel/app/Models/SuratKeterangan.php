<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeterangan extends Model
{
    protected $table = 'surat_keterangan';

    protected $fillable = [
        'data_magang_id', 'nomor_surat', 'tanggal_surat', 'penempatan', 'tgl_mulai', 'tgl_selesai',
        'nilai_akhir', 'predikat', 'jumlah_penilaian', 'status', 'diterbitkan_pada', 'diterbitkan_oleh',
    ];

    protected $casts = [
        'tanggal_surat'    => 'date',
        'tgl_mulai'        => 'date',
        'tgl_selesai'      => 'date',
        'diterbitkan_pada' => 'datetime',
        'nilai_akhir'      => 'float',
    ];

    private const ROMAWI = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public function dataMagang(): BelongsTo
    {
        return $this->belongsTo(DataMagang::class, 'data_magang_id');
    }

    public function terbit(): bool
    {
        return $this->status === 'terbit';
    }

    /** Syarat terbit: nomor, tanggal, penempatan, periode, dan nilai sudah ada. */
    public function lengkap(): bool
    {
        return filled($this->nomor_surat) && $this->tanggal_surat && filled($this->penempatan)
            && $this->tgl_mulai && $this->tgl_selesai && $this->nilai_akhir !== null;
    }

    /** Contoh: 045/HCD/MAJ/PRAKERIN/XI/2026 (bulan romawi mengikuti tanggal surat). */
    public function nomorLengkap(): string
    {
        $tgl = $this->tanggal_surat ?? today();

        return sprintf(
            '%s/%s/%s/%d',
            $this->nomor_surat,
            config('prakerin.surat_keterangan.kode_nomor', 'HCD/MAJ/PRAKERIN'),
            self::ROMAWI[$tgl->month],
            $tgl->year
        );
    }

    /** "SANGAT BAIK (88,50)" */
    public function teksNilai(): string
    {
        return mb_strtoupper((string) $this->predikat) . ' (' . number_format((float) $this->nilai_akhir, 2, ',', '.') . ')';
    }

    public static function tanggalIndonesia(Carbon $tanggal): string
    {
        return $tanggal->copy()->locale('id')->translatedFormat('j F Y');
    }
}
