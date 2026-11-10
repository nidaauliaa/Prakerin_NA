<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

abstract class ApiController extends Controller
{
    protected function ok(array $isi = [], int $kode = 200): JsonResponse
    {
        return response()->json(['sukses' => true] + $isi, $kode, [], JSON_UNESCAPED_UNICODE);
    }

    protected function gagal(string $pesan, int $kode = 400): never
    {
        throw new HttpResponseException(
            response()->json(['sukses' => false, 'pesan' => $pesan], $kode, [], JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Rentang tanggal dari query: ?bulan=2026-10  ATAU  ?dari=2026-10-01&sampai=2026-10-31
     * Tanpa parameter = 30 hari terakhir. Maksimal 366 hari.
     * Mengembalikan [tanggal_awal, tanggal_akhir] berformat Y-m-d.
     */
    protected function rentang(Request $r): array
    {
        $v = Validator::make($r->query(), [
            'bulan' => 'nullable|date_format:Y-m',
            'dari' => 'nullable|date_format:Y-m-d',
            'sampai' => 'nullable|date_format:Y-m-d',
        ]);
        if ($v->fails()) {
            $this->gagal($v->errors()->first(), 422);
        }

        // Pilih satu: ?bulan=... ATAU ?dari=...&sampai=... (dulu 'bulan' diam-diam menimpa dari/sampai).
        if ($r->filled('bulan') && ($r->filled('dari') || $r->filled('sampai'))) {
            $this->gagal("Pakai salah satu: 'bulan' atau 'dari'/'sampai', jangan keduanya.", 422);
        }

        if ($r->filled('bulan')) {
            $awal = Carbon::createFromFormat('!Y-m', $r->query('bulan'))->startOfMonth();
            $akhir = $awal->copy()->endOfMonth();
        } else {
            $akhir = $r->filled('sampai') ? Carbon::createFromFormat('!Y-m-d', $r->query('sampai')) : Carbon::today();
            $awal = $r->filled('dari') ? Carbon::createFromFormat('!Y-m-d', $r->query('dari')) : $akhir->copy()->subDays(29);
        }

        if ($awal->gt($akhir)) {
            $this->gagal("Parameter 'dari' tidak boleh setelah 'sampai'.", 422);
        }
        if ($awal->copy()->addDays(366)->lt($akhir)) {
            $this->gagal('Rentang tanggal maksimal 366 hari.', 422);
        }

        return [$awal->toDateString(), $akhir->toDateString()];
    }
}