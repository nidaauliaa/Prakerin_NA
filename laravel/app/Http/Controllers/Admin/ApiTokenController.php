<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index()
    {
        return view('admin.api-token.index', [
            'tokens' => ApiToken::orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:150',
            'berlaku_hari' => 'nullable|integer|min:1|max:365',
        ]);

        [$token, $asli] = ApiToken::buat(
            $data['nama'],
            auth('admin')->id(),
            ! empty($data['berlaku_hari']) ? now()->addDays((int) $data['berlaku_hari']) : null,
        );

        // Token asli hanya dikirim lewat flash session: tampil SEKALI lalu hilang.
        return redirect()->route('admin.api-token.index')
            ->with('token_baru', ['nama' => $token->nama, 'token' => $asli]);
    }

    public function cabut(ApiToken $apiToken)
    {
        if (! $apiToken->dicabut_pada) {
            $apiToken->forceFill(['dicabut_pada' => now()])->save();
        }

        return redirect()->route('admin.api-token.index')
            ->with('success', "Token \"{$apiToken->nama}\" sudah dicabut.");
    }
}
