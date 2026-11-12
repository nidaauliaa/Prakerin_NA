<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Lamaran extends Model
{
    use HasFactory;

    protected $table = 'lamaran';

    /**
     * Status lamaran yang dihitung memakai kuota lowongan.
     * Ubah ke ['menunggu', 'diterima'] kalau pelamar yang masih menunggu
     * juga harus mengunci slot.
     */
    public const STATUS_MENGISI_KUOTA = ['diterima'];

    protected $fillable = [
        'uuid', 'kode', 'lowongan_id', 'pelamar_id', 'tipe_magang',
        'nama', 'email', 'no_wa', 'alamat', 'foto_pas', 'asal_sekolah', 'jurusan',
        'pembimbing', 'no_wa_pembimbing', 'cv_path', 'surat_permohonan_path', 'nis_nim',
        'tgl_mulai', 'tgl_selesai',
        'status', 'catatan_admin', 'diputuskan_oleh', 'diputuskan_pada', 'data_magang_id',
    ];

    protected $casts = [
        'tgl_mulai'       => 'date',
        'tgl_selesai'     => 'date',
        'diputuskan_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $lamaran) {
            $lamaran->uuid ??= (string) Str::uuid();
            $lamaran->kode ??= 'LMR-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        });
    }

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'lowongan_id');
    }

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(Pelamar::class, 'pelamar_id');
    }

    public function suratBalasan(): HasOne
    {
        return $this->hasOne(SuratBalasan::class, 'lamaran_id');
    }
}