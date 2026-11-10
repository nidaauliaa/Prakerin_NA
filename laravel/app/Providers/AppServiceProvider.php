<?php

namespace App\Providers;

use App\Models\LogLokasiCuriga;
use App\Models\PermintaanResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pembatas laju per rute. Dulu memakai "throttle:10,1" dkk yang SATU keranjang hitungannya dipakai
        // bersama oleh semua rute (per IP), jadi percobaan login/presensi ikut menghabiskan jatah lupa sandi
        // dan sebaliknya (QC-057: kena 429 pada percobaan kedua). Sekarang tiap rute punya hitungan sendiri.
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)->by('login|'.$r->ip()));
        RateLimiter::for('lupa-sandi', fn (Request $r) => Limit::perMinutes(10, 5)->by('lupa-sandi|'.$r->ip()));
        RateLimiter::for('presensi-verifikasi', fn (Request $r) => Limit::perMinute(30)->by('verifikasi|'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('presensi-simpan', fn (Request $r) => Limit::perMinute(10)->by('simpan|'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('tugas-simpan', fn (Request $r) => Limit::perMinute(10)->by('tugas|'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('api-v1', fn (Request $r) => Limit::perMinute(60)->by('api|'.sha1((string) ($r->bearerToken() ?: $r->header('X-API-Key') ?: $r->ip()))));

        // Angka merah di menu "Akun Peserta" = permintaan lupa sandi yang menunggu admin.
        View::composer('layouts.admin', function ($view) {
            $n = 0;
            try {
                $n = PermintaanResetPassword::where('status', 'menunggu')->count();
            } catch (\Throwable $e) {
                // tabel belum dibuat -> badge kosong, halaman tetap jalan
            }
            $view->with('jumlahResetBaru', $n);
            // Angka merah di menu "Abnormal GPS" = kejadian yang BELUM dilihat admin (hilang setelah halamannya dibuka).
            $lc = 0;
            try {
                $lc = LogLokasiCuriga::whereNull('dilihat_admin_at')->count();
            } catch (\Throwable $e) {
                // tabel belum dibuat -> badge kosong
            }
            $view->with('jumlahLokasiCuriga', $lc);
        });
    }
}