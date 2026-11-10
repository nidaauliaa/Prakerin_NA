<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PenautanPeserta;
use Illuminate\Console\Command;

class TautkanPeserta extends Command
{
    protected $signature = 'prakerin:tautkan {email : Email akun peserta} {nisNim : NIM/NISN di data magang} {--timpa-nama : Ganti nama akun dengan nama resmi dari Excel}';

    protected $description = 'Tautkan akun peserta (yang daftar sendiri) ke data magang dari Excel lewat NIM/NISN.';

    public function handle(PenautanPeserta $penautan): int
    {
        $akun = User::where('email', $this->argument('email'))->first();
        if (! $akun) {
            $this->error('Akun dengan email itu tidak ditemukan.');
            return self::FAILURE;
        }

        try {
            $data = $penautan->jalankan($akun, (string) $this->argument('nisNim'), (bool) $this->option('timpa-nama'));
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Berhasil: {$akun->email} ditautkan ke {$data->nama} ({$data->nis_nim}). Kartu ID: {$data->uuid}");

        return self::SUCCESS;
    }
}
