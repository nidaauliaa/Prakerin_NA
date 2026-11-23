<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Halaman Profil peserta: data diri diisi SEKALI di sini (atau lewat lamaran pertama),
 * lalu dipakai semua lamaran. Peserta jalur internal wajib mengisinya agar bisa dicari HRD (nama / NIS-NIM).
 */
class ProfilController extends Controller
{
    private const HP = '/^\+?\d{6,15}$/';

    public function edit()
    {
        $pelamar = Auth::guard('pelamar')->user();

        return view('pelamar.profil', [
            'pelamar'  => $pelamar,
            'sumber'   => $pelamar->profil() ?? $pelamar,   // isian awal (data lama dari lamaran pertama bila ada)
            'kunciNis' => $pelamar->sudahDiterima(),        // NIS/NIM = username presensi, tak boleh diubah setelah diterima
        ]);
    }

    public function update(Request $r)
    {
        $pelamar  = Auth::guard('pelamar')->user();
        $sumber   = $pelamar->profil() ?? $pelamar;
        $kunciNis = $pelamar->sudahDiterima();

        $bersih = fn ($v) => $v ? preg_replace('/[\s\-\.\(\)]/', '', (string) $v) : null;
        $r->merge([
            'no_wa'            => $bersih($r->no_wa),
            'no_wa_pembimbing' => $bersih($r->no_wa_pembimbing),
            'nis_nim'          => $kunciNis ? $pelamar->nis_nim : ($r->nis_nim ? preg_replace('/\s+/', '', $r->nis_nim) : $r->nis_nim),
        ]);

        $smk = $r->tipe_magang === 'smk';

        $nis = ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._\/-]+$/'];
        if (! $kunciNis) {
            $nis[] = Rule::unique('pelamar', 'nis_nim')->ignore($pelamar->id);
            $nis[] = 'unique:users,nis_nim';
            $nis[] = 'unique:data_magang,nis_nim';
        }

        $data = $r->validate([
            'tipe_magang'      => ['required', 'in:smk,kuliah'],
            'nama'             => ['required', 'string', 'max:150'],
            'jenis_kelamin'    => ['required', 'in:L,P'],
            'nis_nim'          => $nis,
            'no_wa'            => ['required', 'regex:' . self::HP],
            'alamat'           => ['required', 'string', 'max:1000'],
            'asal_sekolah'     => ['required', 'string', 'max:150'],
            'jurusan'          => ['required', 'string', 'max:150'],
            'pembimbing'       => [$smk ? 'required' : 'nullable', 'string', 'max:150'],
            'no_wa_pembimbing' => [$smk ? 'required' : 'nullable', 'regex:' . self::HP],
            'foto_pas'         => [filled($sumber->foto_pas) ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'cv'               => [(! $smk && blank($sumber->cv_path)) ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'required'  => ':attribute wajib diisi.',
            'regex'     => ':attribute tidak valid (NIS/NIM: huruf, angka, titik, garis miring, strip tanpa spasi; no. HP: 6-15 digit angka).',
            'unique'    => ':attribute sudah terdaftar. Periksa kembali penulisannya.',
            'image'     => ':attribute harus berupa gambar (JPG/PNG).',
            'mimes'     => 'Format :attribute harus :values.',
            'max'       => ':attribute terlalu besar / panjang.',
            'in'        => ':attribute tidak valid.',
        ], [
            'tipe_magang' => 'Jenjang', 'nama' => 'Nama lengkap', 'jenis_kelamin' => 'Jenis kelamin', 'nis_nim' => 'NIS/NIM',
            'no_wa' => 'No. WhatsApp', 'alamat' => 'Alamat', 'asal_sekolah' => 'Asal sekolah/kampus', 'jurusan' => 'Jurusan',
            'pembimbing' => 'Guru/dosen pembimbing', 'no_wa_pembimbing' => 'No. HP pembimbing',
            'foto_pas' => 'Foto pas', 'cv' => 'CV / portofolio', 'surat_permohonan' => 'Surat pengajuan',
        ]);

        // Berkas disimpan di disk PRIVAT, sama seperti berkas lamaran. Berkas lama dibiarkan
        // (mungkin masih dirujuk lamaran yang sudah terkirim).
        $simpan = $r->only(['tipe_magang', 'nama', 'jenis_kelamin', 'nis_nim', 'no_wa', 'alamat', 'asal_sekolah', 'jurusan', 'pembimbing', 'no_wa_pembimbing']);
        $r->hasFile('foto_pas') && $simpan['foto_pas'] = $r->file('foto_pas')->store('lamaran/foto', 'local');
        $r->hasFile('cv') && $simpan['cv_path'] = $r->file('cv')->store('lamaran/cv', 'local');
        $r->hasFile('surat_permohonan') && $simpan['surat_permohonan_path'] = $r->file('surat_permohonan')->store('lamaran/surat', 'local');

        // Data lama yang hanya ada di lamaran pertama ikut dibawa agar tidak hilang.
        foreach (['foto_pas', 'cv_path', 'surat_permohonan_path'] as $k) {
            if (! isset($simpan[$k]) && blank($pelamar->{$k}) && filled($sumber->{$k})) {
                $simpan[$k] = $sumber->{$k};
            }
        }

        $pelamar->update($simpan);

        // Datang dari alur "Kirim Lamaran"? Lanjutkan ke konfirmasi lamaran.
        if (session()->has('lamaran_draft')) {
            return redirect()->route('lamaran.form')->with('sukses_foto', 'Profil tersimpan. Lanjutkan pengiriman lamaran Anda.');
        }

        return redirect()->route('pelamar.dashboard')->with('sukses_foto', 'Profil berhasil disimpan.');
    }
}
