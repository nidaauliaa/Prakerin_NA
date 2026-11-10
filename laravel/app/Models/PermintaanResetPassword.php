<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanResetPassword extends Model
{
    protected $table = 'permintaan_reset_password';

    protected $fillable = [
        'user_id', 'nama', 'nis_nim', 'divisi', 'alasan', 'status',
        'ditangani_oleh', 'ditangani_at', 'catatan_admin', 'dikonfirmasi_at',
    ];

    protected $casts = [
        'ditangani_at' => 'datetime',
        'dikonfirmasi_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}