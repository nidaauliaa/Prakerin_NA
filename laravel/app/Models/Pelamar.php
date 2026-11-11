<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelamar extends Authenticatable
{
    protected $table = 'pelamar';

    protected $fillable = ['nama', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'pelamar_id');
    }
}