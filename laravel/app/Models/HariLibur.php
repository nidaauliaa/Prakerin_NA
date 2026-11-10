<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = ['nama', 'jenis', 'jenis_label', 'tanggal_mulai', 'tanggal_selesai', 'keterangan'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public const JENIS = [
        'cuti_bersama' => 'Cuti Bersama',
        'libur_nasional' => 'Hari Libur Nasional',
        'lainnya' => 'Lainnya (tulis sendiri)',
    ];

    public function pengganti()
    {
        return $this->hasMany(HariPengganti::class)->orderBy('tanggal');
    }

    /** Label jenis untuk ditampilkan: "Cuti Bersama", "Hari Libur Nasional", atau teks yang ditulis admin. */
    public function labelJenis(): string
    {
        if ($this->jenis === 'lainnya') {
            return $this->jenis_label ?: 'Libur';
        }

        return self::JENIS[$this->jenis] ?? 'Libur';
    }

    public function lamaHari(): int
    {
        return (int) round($this->tanggal_mulai->copy()->startOfDay()
            ->diffInDays($this->tanggal_selesai->copy()->startOfDay(), true)) + 1;
    }
}
