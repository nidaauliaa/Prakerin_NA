<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormPenilaian extends Model
{
    protected $table = 'form_penilaian';

    protected $fillable = ['kode', 'judul', 'deskripsi', 'departemen_id', 'status', 'dibuat_oleh'];

    protected static function booted(): void
    {
        static::creating(function (self $form) {
            $form->kode ??= Str::lower(Str::random(24));   // sulit ditebak, dipakai sebagai link
        });
    }

    public function kriteria(): HasMany
    {
        return $this->hasMany(KriteriaPenilaian::class, 'form_penilaian_id')->orderBy('urutan')->orderBy('id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'form_penilaian_id');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    public function dibuka(): bool
    {
        return $this->status === 'dibuka';
    }

    public function link(): string
    {
        return route('penilaian.isi', $this->kode);
    }

    /** Peserta yang masih bisa dinilai lewat form ini (belum pernah dinilai di form ini). */
    public function pesertaBelumDinilai()
    {
        return DataMagang::query()
            ->when($this->departemen_id, fn ($q, $id) => $q->where('departemen_id', $id))
            ->whereNotIn('id', $this->penilaian()->select('data_magang_id'))
            ->orderBy('nama');
    }
}
