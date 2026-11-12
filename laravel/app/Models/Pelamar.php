<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelamar extends Authenticatable
{
    /** Batas jumlah lamaran per pelamar (riwayat). */
    public const MAKS_LAMARAN = 2;

    protected $table = 'pelamar';

    protected $fillable = ['nama', 'email', 'password', 'foto_profil'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'pelamar_id');
    }

    /** Kata pertama nama lengkap, untuk sapaan "Halo, ...". */
    public function getNamaDepanAttribute(): string
    {
        $kata = preg_split('/\s+/u', trim((string) $this->nama), -1, PREG_SPLIT_NO_EMPTY);

        return $kata[0] ?? '';
    }

    public function getInisialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->nama_depan, 0, 1)) ?: '?';
    }

    /** Foto profil yang dipilih sendiri; kalau belum ada, pakai foto pas dari lamaran terakhir. */
    public function fotoPath(): ?string
    {
        return $this->foto_profil ?: $this->lamaran()->latest('id')->value('foto_pas');
    }

    public function sisaLamaran(): int
    {
        return max(0, self::MAKS_LAMARAN - $this->lamaran()->count());
    }
}
