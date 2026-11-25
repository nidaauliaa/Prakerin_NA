<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lowongan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lowongan';

    protected $fillable = [
        'slug', 'judul', 'tipe_magang', 'departemen_id', 'divisi_id', 'deskripsi',
        'jurusan_diterima', 'kuota', 'status',
        'batas_pendaftaran', 'dibuat_oleh',
    ];

    protected $casts = [
        'kuota'             => 'integer',
        'batas_pendaftaran' => 'date',
    ];

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'lowongan_id');
    }

    /** Lowongan yang tampil di halaman publik. */
    public function scopeDibuka(Builder $query): Builder
    {
        return $query->where('status', 'dibuka')
            ->where(function (Builder $q) {
                $q->whereNull('batas_pendaftaran')
                  ->orWhereDate('batas_pendaftaran', '>=', today());
            });
    }

    public function bisaDilamar(): bool
    {
        return $this->status === 'dibuka'
            && ($this->batas_pendaftaran === null || $this->batas_pendaftaran->gte(today()));
    }

    /**
     * Sama dengan "Daily Occupancy Engine" di prototype:
     * cek tiap hari di rentang yang diajukan, kalau jumlah lamaran yang sudah
     * mengisi kuota >= kuota maka hari itu PENUH.
     *
     * @return array{dari: Carbon, sampai: Carbon}|null  null = tersedia
     */
    public function cekKetersediaan(Carbon $mulai, Carbon $selesai): ?array
    {
        $terisi = $this->lamaran()
            ->whereIn('status', Lamaran::STATUS_MENGISI_KUOTA)
            ->whereDate('tgl_mulai', '<=', $selesai)
            ->whereDate('tgl_selesai', '>=', $mulai)
            ->get(['tgl_mulai', 'tgl_selesai']);

        if ($terisi->isEmpty()) {
            return null;
        }

        $dari = $sampai = null;

        for ($hari = $mulai->copy()->startOfDay(); $hari->lte($selesai); $hari->addDay()) {
            $jumlah = $terisi->filter(
                fn ($l) => $hari->gte($l->tgl_mulai->startOfDay()) && $hari->lte($l->tgl_selesai->endOfDay())
            )->count();

            if ($jumlah >= $this->kuota) {
                $dari ??= $hari->copy();
                $sampai = $hari->copy();
            }
        }

        return $dari ? ['dari' => $dari, 'sampai' => $sampai] : null;
    }
}