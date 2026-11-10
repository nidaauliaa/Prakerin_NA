<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && $user->wajib_ganti_password) {
            return redirect()->route('password.ganti')
                ->with('info', 'Demi keamanan, silakan ganti kata sandi awal Anda terlebih dahulu.');
        }

        return $next($request);
    }
}