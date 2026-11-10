<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = [
        'nama', 'token_hash', 'token_awal', 'dibuat_oleh',
        'berlaku_sampai', 'terakhir_dipakai', 'dicabut_pada',
    ];

    protected $casts = [
        'berlaku_sampai' => 'datetime',
        'terakhir_dipakai' => 'datetime',
        'dicabut_pada' => 'datetime',
    ];

    /** Buat token baru. Mengembalikan [model, token_asli]. Token asli TIDAK disimpan. */
    public static function buat(string $nama, ?int $adminId, ?Carbon $berlakuSampai = null): array
    {
        $asli = 'pk_' . Str::random(40);

        $model = static::create([
            'nama' => $nama,
            'token_hash' => hash('sha256', $asli),
            'token_awal' => substr($asli, 0, 8),
            'dibuat_oleh' => $adminId,
            'berlaku_sampai' => $berlakuSampai,
        ]);

        return [$model, $asli];
    }

    /** Cari token aktif dari teks token asli; null bila tidak ada/dicabut/kedaluwarsa. */
    public static function cari(string $asli): ?self
    {
        $t = static::where('token_hash', hash('sha256', $asli))->first();

        return ($t && $t->aktif()) ? $t : null;
    }

    public function aktif(): bool
    {
        if ($this->dicabut_pada) {
            return false;
        }

        return ! ($this->berlaku_sampai && $this->berlaku_sampai->isPast());
    }

    public function statusTeks(): string
    {
        if ($this->dicabut_pada) {
            return 'Dicabut';
        }

        return ($this->berlaku_sampai && $this->berlaku_sampai->isPast()) ? 'Kedaluwarsa' : 'Aktif';
    }
}
