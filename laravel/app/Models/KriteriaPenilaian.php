<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaPenilaian extends Model
{
    protected $table = 'form_penilaian_kriteria';

    protected $fillable = ['form_penilaian_id', 'nama', 'keterangan', 'bobot', 'urutan'];
}
