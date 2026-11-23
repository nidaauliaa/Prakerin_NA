<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Pelamar yang belum login diarahkan ke login PRAKERIN (bukan login absensi). */
class WajibLoginPelamar
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('pelamar')->check()) {
            return redirect()->route('pelamar.login')
                ->with('info', 'Silakan masuk atau daftar terlebih dahulu untuk melanjutkan lamaran.');
        }

        // Akun portal yang dibuatkan HRD (jalur internal): wajib ganti sandi awal dulu.
        if (Auth::guard('pelamar')->user()->wajib_ganti_password && ! $request->routeIs('pelamar.password.*')) {
            return redirect()->route('pelamar.password.ganti')
                ->with('info', 'Demi keamanan, ganti kata sandi awal Anda terlebih dahulu.');
        }

        return $next($request);
    }
}