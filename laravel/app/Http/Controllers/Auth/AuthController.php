<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PermintaanResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ==== Halaman ====

    public function tampilLanding()
    {
        return view('auth.landing');
    }

    public function tampilMasuk()
    {
        return view('auth.masuk');
    }

    // ==== Aksi: MASUK (Login) ====
    // Peserta masuk dengan NIS/NIM (kolom users.nis_nim). Email tetap diterima untuk akun lama.
    // Admin masuk dengan email.

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = Admin::where('email', $data['identifier'])->first();

        if ($admin && Hash::check($data['password'], $admin->password)) {

            // Pastikan guard peserta logout
            Auth::guard('web')->logout();

            // Login menggunakan guard ADMIN
            Auth::guard('admin')->login($admin);

            $request->session()->regenerate();

            return redirect()->route('admin.presensi.index');
        }


        /*
        |--------------------------------------------------------------------------
        | PESERTA / MENTOR
        |--------------------------------------------------------------------------
        */

        $identifier = trim($data['identifier']);

        $user = User::where('nis_nim', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {

            // Sandi sudah direset admin tapi peserta masih mengetik sandi lama -> arahkan ke sandi awal.
            if ($user && $this->resetBelumDikonfirmasi($user)) {
                throw ValidationException::withMessages([
                    'identifier' => 'Kata sandi akun ini sudah direset admin. Masuk dengan NIS/NIM dan kata sandi awal (seperti saat pertama kali login).',
                ]);
            }

            throw ValidationException::withMessages([
                'identifier' => 'NIS/NIM atau kata sandi salah.',
            ]);
        }

        // Pastikan guard admin logout
        Auth::guard('admin')->logout();

        // Login peserta
        Auth::guard('web')->login(
            $user,
            $request->boolean('ingat')
        );

        $request->session()->regenerate();

        // Sandi awal (12345678) wajib diganti sebelum memakai aplikasi.
        if ($user->wajib_ganti_password) {
            // Reset oleh admin berhasil: beri tahu peserta (sekali saja).
            if ($this->resetBelumDikonfirmasi($user)) {
                PermintaanResetPassword::where('user_id', $user->id)->where('status', 'selesai')
                    ->whereNull('dikonfirmasi_at')->update(['dikonfirmasi_at' => now()]);

                return redirect()->route('password.ganti')
                    ->with('sukses', 'Reset kata sandi berhasil. Riwayat absen dan tugasmu tetap aman. Buat kata sandi baru di bawah ini.');
            }

            return redirect()->route('password.ganti');
        }

        return redirect()->intended(route('presensi.index'));
    }

    /** Ada permintaan lupa sandi yang sudah diselesaikan admin dan belum "dikonfirmasi" peserta. */
    private function resetBelumDikonfirmasi(User $user): bool
    {
        try {
            return PermintaanResetPassword::where('user_id', $user->id)
                ->where('status', 'selesai')->whereNull('dikonfirmasi_at')->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ==== Aksi: KELUAR ADMIN ====

    public function keluarAdmin(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ==== Aksi: KELUAR PESERTA ====

    public function keluar(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}