<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataMagang extends Model
{
    protected $table = 'data_magang';

    protected $fillable = [
        'nama', 'tipe_magang', 'jenis_kelamin',
        'no_ktp', 'no_wa', 'alamat', 'tempat_lahir', 'tanggal_lahir',
        'nis_nim', 'asal_sekolah', 'jurusan', 'departemen_id', 'divisi',
        'uuid', 'user_id', 'ditautkan_pada', 'foto_pas', 'pembimbing',
        'no_wa_pembimbing', 'cv_path', 'surat_permohonan_path',
        'periode_magang', 'status_kehadiran_awal', 'tempat_prakerin',
        'tgl_mulai', 'tgl_selesai', 'keterangan', 'status_magang',
    ];

    protected $casts = [
        'ditautkan_pada' => 'datetime',
        'tanggal_lahir' => 'date',
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    /**
     * Peserta AKTIF = status_magang ACTIVE dan tanggal selesai belum lewat (atau belum diisi).
     * TIDAK AKTIF (arsip) = status bukan ACTIVE, atau tanggal selesai sudah lewat.
     */
    /**
     * Magang yang tanggal selesainya sudah lewat tetapi statusnya masih ACTIVE ditandai SELESAI,
     * supaya di Arsip dan halaman detail statusnya tidak lagi tertulis ACTIVE (QC-108).
     * Dipanggil saat admin membuka Presensi / Peserta Aktif / Arsip. Satu query UPDATE, aman diulang.
     */
    public static function tandaiSelesai(): int
    {
        try {
            return static::where('status_magang', 'ACTIVE')
                ->whereNotNull('tgl_selesai')
                ->where('tgl_selesai', '<', today()->toDateString())
                ->update(['status_magang' => 'SELESAI']);
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    public function scopeAktif($q)
    {
        return $q->where('status_magang', 'ACTIVE')
            ->where(fn ($w) => $w->whereNull('tgl_selesai')->orWhere('tgl_selesai', '>=', today()->toDateString()));
    }

    public function scopeTidakAktif($q)
    {
        return $q->where(fn ($w) => $w->where('status_magang', '!=', 'ACTIVE')
            ->orWhere('tgl_selesai', '<', today()->toDateString()));
    }

    public function isAktif(): bool
    {
        return strtoupper((string) $this->status_magang) === 'ACTIVE'
            && ! ($this->tgl_selesai && $this->tgl_selesai->copy()->startOfDay()->lt(today()));
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'data_magang_id');
    }

    public function suratKeterangan()
    {
        return $this->hasOne(SuratKeterangan::class, 'data_magang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
