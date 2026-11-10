<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\PermintaanResetPassword;
use App\Models\User;
use Illuminate\Http\Request;

class LupaSandiController extends Controller
{
    public function tampil()
    {
        return view('auth.lupa-sandi', ['departemen' => Departemen::orderBy('nama')->get()]);
    }

    public function kirim(Request $request)
    {
        $d = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nim' => ['required', 'string', 'max:50'],
            'divisi' => ['required', 'string', 'max:100'],
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nim.required' => 'NIS/NIM wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'alasan.required' => 'Alasan lupa kata sandi wajib diisi.',
            'alasan.min' => 'Jelaskan alasan lupa kata sandi minimal 10 karakter.',
            'alasan.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $nim = trim($d['nim']);
        $user = User::where('nis_nim', $nim)->where('peran', 'peserta')->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'nim' => 'NIS/NIM tidak terdaftar. Periksa kembali, atau hubungi admin langsung.',
            ]);
        }

        // Satu permintaan aktif per akun, supaya admin tidak dibanjiri.
        $sudahAda = PermintaanResetPassword::where('user_id', $user->id)->where('status', 'menunggu')->exists();

        if ($sudahAda) {
            // Tetap di form dan tampilkan peringatan merah yang jelas (dulu hanya pesan hijau di halaman login
            // yang mirip pesan "berhasil", jadi penguji tidak tahu permintaan kedua ditolak) - QC-056.
            return back()->withInput()->withErrors([
                'nim' => 'Permintaan reset kata sandi untuk NIS/NIM ini sudah dikirim dan masih menunggu admin. Tidak perlu mengirim lagi; hubungi admin bila mendesak.',
            ]);
        }

        PermintaanResetPassword::create([
            'user_id' => $user->id,
            'nama' => trim($d['nama']),
            'nis_nim' => $nim,
            'divisi' => trim($d['divisi']),
            'alasan' => trim($d['alasan']),
        ]);

        return redirect()->route('login')->with(
            'sukses',
            'Permintaan terkirim ke admin. Setelah admin mereset, masuk dengan NIS/NIM dan kata sandi awal (sama seperti saat pertama kali login), lalu buat kata sandi baru.'
        );
    }
}