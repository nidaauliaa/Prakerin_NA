<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = [
        'form_penilaian_id', 'data_magang_id', 'nama_penilai', 'jabatan_penilai',
        'nilai_akhir', 'predikat', 'catatan',
    ];

    protected $casts = ['nilai_akhir' => 'float'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormPenilaian::class, 'form_penilaian_id');
    }

    public function dataMagang(): BelongsTo
    {
        return $this->belongsTo(DataMagang::class, 'data_magang_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(PenilaianNilai::class, 'penilaian_id');
    }

    /**
     * Rata-rata berbobot dari pasangan [nilai, bobot]. Hasil 0-100, dibulatkan 2 desimal.
     *
     * @param  iterable<array{0:int|float,1:int|float}>  $pasangan
     */
    public static function hitung(iterable $pasangan): float
    {
        $jumlah = 0.0;
        $bobot  = 0.0;

        foreach ($pasangan as [$nilai, $b]) {
            $jumlah += $nilai * $b;
            $bobot  += $b;
        }

        return $bobot > 0 ? round($jumlah / $bobot, 2) : 0.0;
    }

    /** Predikat dari config('prakerin.penilaian.predikat'): daftar [batas_bawah, label], dari tinggi ke rendah. */
    public static function predikat(float $nilai): string
    {
        $daftar = config('prakerin.penilaian.predikat', []);
        usort($daftar, fn ($a, $b) => $b[0] <=> $a[0]);

        foreach ($daftar as [$batas, $label]) {
            if ($nilai >= $batas) {
                return $label;
            }
        }

        return $daftar ? end($daftar)[1] : '-';
    }
}
