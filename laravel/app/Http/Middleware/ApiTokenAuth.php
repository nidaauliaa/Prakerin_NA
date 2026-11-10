<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Memeriksa token API dari header:
//   Authorization: Bearer <token>     ATAU     X-API-Key: <token>
// Token di URL (?token=...) sengaja TIDAK didukung karena tercatat di log server.
class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $asli = $request->bearerToken() ?: $request->header('X-API-Key');
        $token = $asli ? ApiToken::cari($asli) : null;

        if (! $token) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Token API tidak ada, tidak valid, kedaluwarsa, atau sudah dicabut.',
            ], 401, ['WWW-Authenticate' => 'Bearer']);
        }

        // Catat pemakaian paling sering sekali per menit agar tidak membebani database.
        if (! $token->terakhir_dipakai || $token->terakhir_dipakai->lt(now()->subMinute())) {
            $token->forceFill(['terakhir_dipakai' => now()])->save();
        }

        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
