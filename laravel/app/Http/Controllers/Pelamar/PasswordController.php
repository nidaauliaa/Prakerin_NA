<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** Ganti sandi awal akun portal (peserta jalur internal yang dibuatkan HRD wajib menggantinya). */
class PasswordController extends Controller
{
    public function tampil()
    {
        return view('pelamar.ganti-password', ['pelamar' => Auth::guard('pelamar')->user()]);
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', Rule::notIn([User::PASSWORD_DEFAULT])],
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.not_in'    => 'Kata sandi baru tidak boleh sama dengan kata sandi awal.',
        ]);

        Auth::guard('pelamar')->user()->update([
            'password'             => $data['password'],   // di-hash oleh cast
            'wajib_ganti_password' => false,
        ]);

        return redirect()->route('pelamar.dashboard')->with('sukses_foto', 'Kata sandi berhasil diganti.');
    }
}
