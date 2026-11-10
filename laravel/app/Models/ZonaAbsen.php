<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaAbsen extends Model
{
    protected $table = 'zona_absen';

    protected $fillable = ['kode', 'nama', 'lat', 'lng', 'radius'];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'radius' => 'integer',
    ];

    private static ?array $memo = null;

    /**
     * Semua zona sebagai array ['kode','nama','lat','lng','radius'] (bentuk sama seperti config('prakerin.pos')).
     * Bila tabel belum dimigrasi (error query), jatuh kembali ke config supaya web tidak error.
     */
    public static function daftar(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        try {
            return self::$memo = static::orderBy('id')->get()->map(fn ($z) => [
                'kode' => $z->kode,
                'nama' => $z->nama,
                'lat' => (float) $z->lat,
                'lng' => (float) $z->lng,
                'radius' => (int) $z->radius,
            ])->all();
        } catch (\Throwable $e) {
            return self::$memo = (array) config('prakerin.pos', []);
        }
    }

    public static function lupakan(): void
    {
        self::$memo = null;
    }

    /** Kode berikutnya yang belum dipakai: pos-6, pos-7, dst. */
    public static function kodeBaru(): string
    {
        $maks = 0;
        foreach (static::pluck('kode') as $k) {
            if (preg_match('/^pos-(\d+)$/', $k, $m)) {
                $maks = max($maks, (int) $m[1]);
            }
        }
        $n = $maks + 1;
        while (static::where('kode', "pos-$n")->exists()) {
            $n++;
        }

        return "pos-$n";
    }
}