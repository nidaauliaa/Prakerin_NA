<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Divisi / Section di bawah suatu Departemen. Lowongan dibuka per divisi/section. */
class Divisi extends Model
{
    protected $table = 'divisi';

    protected $fillable = ['departemen_id', 'nama', 'jurusan'];

    public const LAINNYA = 'lainnya';

    /** Aturan validasi dropdown section + isian "Lainnya" (dipakai form lowongan & peserta internal). */
    public static function aturanPilihan(): array
    {
        return [
            'divisi_id' => ['required', function ($attr, $val, $fail) {
                if ($val !== self::LAINNYA && ! self::whereKey($val)->exists()) {
                    $fail('Posisi yang dipilih tidak valid.');
                }
            }],
            'divisi_baru_nama'          => ['nullable', 'required_if:divisi_id,' . self::LAINNYA, 'string', 'max:150'],
            'divisi_baru_departemen_id' => ['nullable', 'required_if:divisi_id,' . self::LAINNYA, 'integer', 'exists:departemen,id'],
        ];
    }

    public static function pesanPilihan(): array
    {
        return [
            'divisi_id.required'                  => 'Nama posisi wajib dipilih.',
            'divisi_baru_nama.required_if'        => 'Nama section baru wajib diisi.',
            'divisi_baru_departemen_id.required_if' => 'Departemen untuk section baru wajib dipilih.',
        ];
    }

    /**
     * Ubah pilihan dropdown jadi record Divisi. Kalau "Lainnya", section baru dibuat (atau dipakai yang sudah ada
     * dengan nama sama di departemen itu) sehingga langsung muncul di dropdown berikutnya.
     */
    public static function dariPilihan(array $data, ?string $jurusan = null): self
    {
        if (($data['divisi_id'] ?? null) !== self::LAINNYA) {
            return self::findOrFail((int) $data['divisi_id']);
        }

        return self::firstOrCreate(
            ['departemen_id' => (int) $data['divisi_baru_departemen_id'], 'nama' => trim($data['divisi_baru_nama'])],
            ['jurusan' => $jurusan ?: null]
        );
    }

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
