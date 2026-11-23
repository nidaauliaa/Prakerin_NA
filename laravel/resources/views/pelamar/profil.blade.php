@extends('layouts.public')

@section('title', 'Profil Peserta - PT Mekar Armada Jaya')

@section('content')
@php
    $inp  = 'w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue';
    $lbl  = 'block text-xs font-semibold text-slate-700 mb-1';
    $v    = fn (string $k) => old($k, $sumber->{$k});
    $tipe = $v('tipe_magang') ?: 'kuliah';
    $smk  = $tipe === 'smk';
@endphp
<section class="py-10 bg-slate-100 min-h-[75vh]">
    <div class="max-w-3xl mx-auto px-4">
        <a href="{{ route('pelamar.dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-blue">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard Peserta
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-2">Profil Peserta</h1>
        <p class="text-xs text-slate-500 mt-1">Data diri cukup diisi sekali di sini, lalu dipakai untuk semua lamaran dan untuk data presensi saat magang.</p>

        @if (session('info'))
            <div class="mt-4 p-3.5 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-4 p-3.5 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                <strong>Mohon periksa isian berikut:</strong>
                <ul class="list-disc ml-4 mt-1 space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="formProfil" method="POST" action="{{ route('pelamar.profil.update') }}" enctype="multipart/form-data"
              class="mt-4 bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-200">
            @csrf

            <div class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900"><i class="fa-regular fa-id-card text-brand-blue mr-2"></i>Data Diri</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="{{ $lbl }}">Jenjang <span class="text-rose-500">*</span></label>
                        <select name="tipe_magang" id="fTipe" required class="{{ $inp }}">
                            <option value="smk" @selected($tipe === 'smk')>Anak SMK</option>
                            <option value="kuliah" @selected($tipe === 'kuliah')>Mahasiswa (D3/S1)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $lbl }}">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $sumber->nama ?: $pelamar->nama) }}" required maxlength="150" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="{{ $inp }}">
                            <option value="">Pilih</option>
                            <option value="L" @selected($v('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected($v('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $lbl }}"><span id="lblNis">{{ $smk ? 'NIS (Nomor Induk Siswa)' : 'NIM (Nomor Induk Mahasiswa)' }}</span> <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nim" value="{{ $v('nis_nim') }}" required maxlength="50" autocomplete="off" @readonly($kunciNis) class="{{ $inp }} {{ $kunciNis ? 'bg-slate-100 text-slate-500' : '' }}">
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ $kunciNis ? 'NIS/NIM tidak dapat diubah karena dipakai sebagai username presensi.' : 'Dipakai sebagai username login presensi saat magang, pastikan penulisannya benar.' }}
                        </p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Email</label>
                        <input type="text" value="{{ $pelamar->email }}" readonly class="{{ $inp }} bg-slate-100 text-slate-500">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">No. WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="tel" name="no_wa" value="{{ $v('no_wa') }}" required inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx (6-15 digit)" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}"><span id="lblAsal">{{ $smk ? 'Asal Sekolah' : 'Asal Kampus' }}</span> <span class="text-rose-500">*</span></label>
                        <input type="text" name="asal_sekolah" value="{{ $v('asal_sekolah') }}" required maxlength="150" class="{{ $inp }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $lbl }}">Jurusan / Program Studi <span class="text-rose-500">*</span></label>
                        <input type="text" name="jurusan" value="{{ $v('jurusan') }}" required maxlength="150" class="{{ $inp }}">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="{{ $lbl }}">Alamat <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" rows="2" required maxlength="1000" class="{{ $inp }}">{{ $v('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900"><i class="fa-solid fa-user-tie text-brand-blue mr-2"></i>Pembimbing</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $lbl }}"><span id="lblPemb">{{ $smk ? 'Guru Pembimbing' : 'Dosen Pembimbing' }}</span> <span id="wajibPemb" class="text-rose-500 {{ $smk ? '' : 'hidden' }}">*</span></label>
                        <input type="text" name="pembimbing" id="fPemb" value="{{ $v('pembimbing') }}" maxlength="150" @required($smk) class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">No. HP Pembimbing <span id="wajibPembHp" class="text-rose-500 {{ $smk ? '' : 'hidden' }}">*</span></label>
                        <input type="tel" name="no_wa_pembimbing" id="fPembHp" value="{{ $v('no_wa_pembimbing') }}" inputmode="tel" maxlength="20" @required($smk) placeholder="6-15 digit" class="{{ $inp }}">
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900"><i class="fa-solid fa-paperclip text-brand-blue mr-2"></i>Berkas</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="{{ $lbl }}">Foto Pas <span class="text-rose-500">*</span> <span class="font-normal text-slate-400">(JPG/PNG, maks 2 MB)</span></label>
                        <input type="file" name="foto_pas" accept="image/png,image/jpeg" @required(blank($sumber->foto_pas)) class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        <p class="text-[11px] mt-1 {{ filled($sumber->foto_pas) ? 'text-emerald-600' : 'text-slate-400' }}">{{ filled($sumber->foto_pas) ? 'Sudah ada. Pilih berkas baru hanya jika ingin mengganti.' : 'Belum ada.' }}</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">CV / Portofolio <span id="wajibCv" class="text-rose-500 {{ $smk ? 'hidden' : '' }}">*</span> <span class="font-normal text-slate-400">(PDF, maks 5 MB)</span></label>
                        <input type="file" name="cv" id="fCv" accept="application/pdf" @required(! $smk && blank($sumber->cv_path)) class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        <p class="text-[11px] mt-1 {{ filled($sumber->cv_path) ? 'text-emerald-600' : 'text-slate-400' }}">{{ filled($sumber->cv_path) ? 'Sudah ada. Pilih berkas baru hanya jika ingin mengganti.' : 'Belum ada.' }}</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Surat Pengajuan Sekolah/Kampus <span class="font-normal text-slate-400">(PDF, opsional)</span></label>
                        <input type="file" name="surat_permohonan" accept="application/pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        <p class="text-[11px] mt-1 {{ filled($sumber->surat_permohonan_path) ? 'text-emerald-600' : 'text-slate-400' }}">{{ filled($sumber->surat_permohonan_path) ? 'Sudah ada.' : 'Belum ada.' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 rounded-b-2xl flex justify-end gap-2">
                <a href="{{ route('pelamar.dashboard') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition">Simpan Profil</button>
            </div>
        </form>
    </div>
</section>

<script>
    (function () {
        const $ = (id) => document.getElementById(id);
        const sudahCv = @json(filled($sumber->cv_path));
        function atur() {
            const smk = $('fTipe').value === 'smk';
            $('lblNis').textContent  = smk ? 'NIS (Nomor Induk Siswa)' : 'NIM (Nomor Induk Mahasiswa)';
            $('lblAsal').textContent = smk ? 'Asal Sekolah' : 'Asal Kampus';
            $('lblPemb').textContent = smk ? 'Guru Pembimbing' : 'Dosen Pembimbing';
            $('wajibPemb').classList.toggle('hidden', !smk);
            $('wajibPembHp').classList.toggle('hidden', !smk);
            $('wajibCv').classList.toggle('hidden', smk);
            $('fPemb').required = smk;
            $('fPembHp').required = smk;
            $('fCv').required = !smk && !sudahCv;
        }
        $('fTipe').addEventListener('change', atur);
        atur();
    })();
</script>
@endsection
