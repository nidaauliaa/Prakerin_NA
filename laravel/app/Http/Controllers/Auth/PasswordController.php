<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function tampil()
    {
        return view('auth.ganti-password', ['wajib' => (bool) Auth::user()->wajib_ganti_password]);
    }

    public function simpan(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8), 'not_in:' . User::PASSWORD_DEFAULT],
        ];
        $pesan = [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.not_in' => 'Kata sandi baru tidak boleh sama dengan kata sandi awal.',
        ];

        // Ganti sandi sukarela (bukan paksaan) wajib menyebut sandi lama.
        if (!$user->wajib_ganti_password) {
            $rules['password_lama'] = ['required', 'current_password'];
            $pesan['password_lama.current_password'] = 'Kata sandi lama salah.';
        }

        $data = $request->validate($rules, $pesan);

        $user->update([
            'password' => $data['password'], // di-hash otomatis oleh cast 'hashed'
            'wajib_ganti_password' => false,
        ]);

        $request->session()->regenerate();

        return redirect()->route('presensi.index')->with('sukses', 'Kata sandi berhasil diganti.');
    }
}