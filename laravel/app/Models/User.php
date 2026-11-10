<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'peran', 'status_akun',
        'departemen_id', 'uuid_kartu', 'foto_profil_url', 'asal_sekolah', 'jurusan',
        'nis_nim', 'wajib_ganti_password', 'pos_kode',
    ];

    /** Sandi awal akun peserta yang dibuat admin / import. Wajib diganti saat login pertama. */
    public const PASSWORD_DEFAULT = '12345678';

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    /**
     * Setiap user baru (registrasi, import Excel, seeder, dll) otomatis
     * mendapat uuid_kartu yang dipakai sebagai isi QR.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid_kartu)) {
                $user->uuid_kartu = (string) Str::uuid();
            }
        });
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    /** Pos (zona absen) milik peserta ini dari tabel zona_absen, atau null bila belum ditentukan/tidak dikenal. */
    public function zona(): ?array
    {
        if (blank($this->pos_kode)) {
            return null;
        }

        foreach (ZonaAbsen::daftar() as $pos) {
            if (($pos['kode'] ?? null) === $this->pos_kode) {
                return $pos;
            }
        }

        return null;
    }

    /**
     * Tanggal selesai magang bila SUDAH LEWAT (hari ini lebih dari tgl_selesai; di hari terakhir
     * peserta masih boleh absen). null = masih berlaku, atau tgl_selesai belum diisi.
     */
    public function masaMagangBerakhir(): ?\Illuminate\Support\Carbon
    {
        $selesai = $this->dataMagang?->tgl_selesai;

        return ($selesai && now()->startOfDay()->gt($selesai->copy()->startOfDay())) ? $selesai : null;
    }

    /**
     * true bila peserta SUDAH TIDAK AKTIF magang (status bukan ACTIVE, atau tanggal selesai sudah lewat).
     * Peserta seperti ini tidak boleh presensi. Peserta tanpa data magang dianggap aktif (aturan lama).
     */
    public function magangTidakAktif(): bool
    {
        $dm = $this->dataMagang;

        return $dm !== null && ! $dm->isAktif();
    }

    public function dataMagang()
    {
        return $this->hasOne(DataMagang::class);
    }

    /**
     * URL foto pas milik akun yang sedang login (lewat rute profil.foto).
     * Jangan pakai foto_profil_url: isinya alamat absolut yang ikut APP_URL saat disimpan, jadi rusak
     * kalau domain/subfolder situs berbeda.
     */
    public function fotoSayaUrl(): ?string
    {
        $path = $this->dataMagang?->foto_pas;

        return filled($path) ? route('profil.foto', ['v' => substr(md5($path), 0, 8)]) : null;
    }

    public function sesiPresensi()
    {
        return $this->hasMany(SesiPresensi::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    public function laporanHarian()
    {
        return $this->hasManyThrough(LaporanHarian::class, SesiPresensi::class);
    }
}