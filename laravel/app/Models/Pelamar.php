<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelamar extends Authenticatable
{
    /** Batas jumlah lamaran per pelamar (riwayat). */
    public const MAKS_LAMARAN = 2;

    protected $table = 'pelamar';

    protected $fillable = [
        'nama', 'email', 'password', 'foto_profil', 'wajib_ganti_password',
        // profil peserta (diisi sendiri di halaman Profil, atau lewat lamaran pertama)
        'tipe_magang', 'jenis_kelamin', 'nis_nim', 'no_wa', 'alamat', 'asal_sekolah', 'jurusan',
        'pembimbing', 'no_wa_pembimbing', 'foto_pas', 'cv_path', 'surat_permohonan_path',
    ];

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

    /** Foto profil yang dipilih sendiri; kalau belum ada, pakai foto pas dari profil / lamaran terakhir. */
    public function fotoPath(): ?string
    {
        return $this->foto_profil ?: $this->foto_pas ?: $this->lamaran()->latest('id')->value('foto_pas');
    }

    /** Profil sudah terisi lengkap (syarat dicari HRD untuk jalur internal, dan untuk melamar). */
    public function profilLengkap(): bool
    {
        foreach (['tipe_magang', 'jenis_kelamin', 'nis_nim', 'no_wa', 'alamat', 'asal_sekolah', 'jurusan', 'foto_pas'] as $kolom) {
            if (blank($this->{$kolom})) {
                return false;
            }
        }

        return $this->tipe_magang === 'smk'
            ? filled($this->pembimbing) && filled($this->no_wa_pembimbing)
            : filled($this->cv_path);
    }

    /**
     * Profil peserta: diisi SEKALI (halaman Profil / lamaran pertama), dipakai semua lamaran.
     * Mengembalikan model yang punya kolom profil (akun ini, atau lamaran pertama untuk data lama).
     */
    public function profil(): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->profilLengkap() ? $this : $this->lamaran()->oldest('id')->first();
    }

    public function sudahDiterima(): bool
    {
        return $this->lamaran()->where('status', 'diterima')->exists();
    }

    public function sisaLamaran(): int
    {
        return max(0, self::MAKS_LAMARAN - $this->lamaran()->count());
    }
}
