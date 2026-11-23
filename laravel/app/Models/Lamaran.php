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
        'uuid', 'kode', 'jalur', 'lowongan_id', 'pelamar_id', 'tipe_magang',
        'nama', 'jenis_kelamin', 'email', 'no_wa', 'alamat', 'foto_pas', 'asal_sekolah', 'jurusan',
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

    public function dataMagang(): BelongsTo
    {
        return $this->belongsTo(DataMagang::class, 'data_magang_id');
    }

    public function adalahInternal(): bool
    {
        return $this->jalur === 'internal';
    }

    /**
     * Satu pelamar hanya bisa diterima di SATU posisi: begitu satu lamaran diterima,
     * lamaran lain miliknya yang masih "menunggu" otomatis ditolak. Mengembalikan jumlahnya.
     */
    public static function tolakLainnyaOtomatis(self $diterima, ?int $adminId = null): int
    {
        if (! $diterima->pelamar_id) {
            return 0;
        }

        return static::where('pelamar_id', $diterima->pelamar_id)
            ->where('id', '!=', $diterima->id)
            ->where('status', 'menunggu')
            ->update([
                'status'          => 'ditolak',
                'catatan_admin'   => 'Otomatis ditolak: Anda sudah diterima di posisi lain (satu pelamar hanya dapat diterima di satu posisi).',
                'diputuskan_oleh' => $adminId,
                'diputuskan_pada' => now(),
            ]);
    }

    public function suratBalasan(): HasOne
    {
        return $this->hasOne(SuratBalasan::class, 'lamaran_id');
    }
}