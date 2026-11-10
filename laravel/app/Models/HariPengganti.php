<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariPengganti extends Model
{
    protected $table = 'hari_pengganti';

    protected $fillable = ['hari_libur_id', 'tanggal'];

    protected $casts = ['tanggal' => 'date'];

    public function hariLibur()
    {
        return $this->belongsTo(HariLibur::class);
    }
}
