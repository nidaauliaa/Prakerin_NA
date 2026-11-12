<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratBalasan extends Model
{
    protected $table = 'surat_balasan';

    protected $fillable = [
        'lamaran_id', 'nomor_surat', 'tanggal_surat', 'ditujukan_kepada',
        'tgl_pengajuan', 'media_pengajuan', 'tgl_mulai', 'tgl_selesai',
        'tgl_induction', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tgl_pengajuan' => 'date',
        'tgl_mulai'     => 'date',
        'tgl_selesai'   => 'date',
        'tgl_induction' => 'date',
    ];

    private const ROMAWI = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public function lamaran(): BelongsTo
    {
        return $this->belongsTo(Lamaran::class, 'lamaran_id');
    }

    /** Contoh: 045/PRAKERIN/HCD/MAJ/XI/2026 (bulan romawi, mengikuti tanggal surat). */
    public function nomorLengkap(): string
    {
        return sprintf(
            '%s/%s/%s/%d',
            $this->nomor_surat,
            config('prakerin.surat_balasan.kode_nomor', 'PRAKERIN/HCD/MAJ'),
            self::ROMAWI[$this->tanggal_surat->month],
            $this->tanggal_surat->year
        );
    }

    /** "11 November 2026" */
    public static function tanggalIndonesia(Carbon $tanggal): string
    {
        return $tanggal->copy()->locale('id')->translatedFormat('j F Y');
    }
}
