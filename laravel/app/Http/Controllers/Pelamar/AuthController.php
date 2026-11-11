<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Models\Pelamar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Login / daftar PELAMAR prakerin (guard `pelamar`), terpisah dari login absensi. */
class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('pelamar')->check()) {
            return $this->setelahMasuk();
        }

        return view('pelamar.login', ['draft' => $this->ringkasanDraft()]);
    }

    public function login(Request $request)
    {
        $kredensial = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('pelamar')->attempt($kredensial, $request->boolean('ingat'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();   // draft lamaran di session tetap terbawa

        return $this->setelahMasuk();
    }

    public function showRegister()
    {
        if (Auth::guard('pelamar')->check()) {
            return $this->setelahMasuk();
        }

        return view('pelamar.register', ['draft' => $this->ringkasanDraft()]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', 'unique:pelamar,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'       => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $pelamar = Pelamar::create([
            'nama'     => $data['nama'],
            'email'    => mb_strtolower($data['email']),
            'password' => $data['password'],   // di-hash otomatis oleh cast
        ]);

        Auth::guard('pelamar')->login($pelamar);
        $request->session()->regenerate();

        return $this->setelahMasuk();
    }

    public function logout(Request $request)
    {
        Auth::guard('pelamar')->logout();
        $request->session()->forget('lamaran_draft');
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }

    private function setelahMasuk()
    {
        return session()->has('lamaran_draft')
            ? redirect()->route('lamaran.form')
            : redirect()->route('beranda');
    }

    /** Ringkasan pilihan posisi + periode untuk ditampilkan di halaman login/daftar. */
    private function ringkasanDraft(): ?array
    {
        $draft    = session('lamaran_draft');
        $lowongan = $draft ? Lowongan::find($draft['lowongan_id']) : null;

        return $lowongan ? [
            'posisi'  => $lowongan->judul,
            'mulai'   => Carbon::parse($draft['tgl_mulai'])->translatedFormat('j M Y'),
            'selesai' => Carbon::parse($draft['tgl_selesai'])->translatedFormat('j M Y'),
        ] : null;
    }
}