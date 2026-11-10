<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogLokasiCuriga extends Model
{
    protected $table = 'log_lokasi_curiga';

    protected $fillable = [
        'user_id', 'sesi_presensi_id', 'sesi', 'tindakan', 'skor', 'alasan',
        'lat', 'lng', 'akurasi', 'device_id', 'ip', 'user_agent',
    ];

    protected $casts = [
        'alasan' => 'array',
        'skor' => 'integer',
        'dilihat_admin_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sesiPresensi(): BelongsTo
    {
        return $this->belongsTo(SesiPresensi::class, 'sesi_presensi_id');
    }
}