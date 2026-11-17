<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianNilai extends Model
{
    protected $table = 'penilaian_nilai';

    public $timestamps = false;

    protected $fillable = ['penilaian_id', 'kriteria_id', 'nama_kriteria', 'bobot', 'nilai'];
}
