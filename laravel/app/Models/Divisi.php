<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Divisi / Section di bawah suatu Departemen. Lowongan dibuka per divisi/section. */
class Divisi extends Model
{
    protected $table = 'divisi';

    protected $fillable = ['departemen_id', 'nama', 'jurusan'];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    public function lowongan(): HasMany
    {
        return $this->hasMany(Lowongan::class, 'divisi_id');
    }

    /** "IT Software (IT)" -- untuk dropdown / tampilan ringkas. */
    public function getNamaLengkapAttribute(): string
    {
        return $this->nama . ' (' . ($this->departemen?->nama ?? '-') . ')';
    }
}
