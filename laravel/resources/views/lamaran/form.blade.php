@extends('layouts.public')

@section('title', 'Lengkapi Data Lamaran - PT Mekar Armada Jaya')

@section('content')
@php $isSmk = $lowongan->tipe_magang === 'smk'; @endphp
<section class="py-12 bg-slate-100 min-h-[70vh]">
    <div class="max-w-2xl mx-auto px-4">
        <a href="{{ route('beranda') }}#lowongan" class="text-xs font-semibold text-slate-500 hover:text-brand-blue">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke daftar lowongan
        </a>

        <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Ringkasan posisi & periode -->
            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-extrabold uppercase mb-1 {{ $isSmk ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-brand-blue' }}">
                    {{ $isSmk ? 'SMK' : 'Mahasiswa' }}
                </span>
                <h1 class="text-xl font-bold text-slate-900">{{ $lowongan->judul }}</h1>

                <div class="grid grid-cols-3 gap-3 mt-4 text-xs">
                    <div class="bg-white border border-slate-200 rounded-lg p-3">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</span>
                        <strong class="text-slate-800">{{ $mulai->translatedFormat('j M Y') }}</strong>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg p-3">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Selesai</span>
                        <strong class="text-slate-800">{{ $selesai->translatedFormat('j M Y') }}</strong>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg p-3">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi</span>
                        <strong class="text-slate-800">{{ (int) $mulai->diffInDays($selesai) }} hari</strong>
                    </div>
                </div>
                <a href="{{ route('beranda') }}#lowongan" class="inline-block mt-3 text-[11px] font-semibold text-brand-blue hover:underline">Ubah posisi / periode</a>
            </div>

            <form id="formLamaran" method="POST" action="{{ route('lamaran.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                @if ($errors->any())
                <div class="p-3.5 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                    <strong>Mohon periksa kembali:</strong>
                    <ul class="list-disc ml-4 mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <!-- Data Diri -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-regular fa-id-card text-brand-blue"></i> Data Diri Pelamar
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $pelamar->nama) }}" required maxlength="150" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                            <select name="jenis_kelamin" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                                <option value="">Pilih</option>
                                <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(dari akun)</span></label>
                            <input type="email" value="{{ $pelamar->email }}" readonly class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-slate-50 text-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                            <input type="tel" name="no_wa" value="{{ old('no_wa') }}" required inputmode="tel" minlength="6" maxlength="20" placeholder="08xxxxxxxxxx (6-15 digit)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" rows="2" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('alamat') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Pas <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(JPG/PNG, maks 2 MB)</span></label>
                        <input type="file" name="foto_pas" accept="image/png,image/jpeg" required class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-semibold hover:file:bg-slate-200">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $isSmk ? 'Asal Sekolah' : 'Asal Kampus' }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}" required maxlength="150" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jurusan <span class="text-rose-500">*</span></label>
                            <input type="text" name="jurusan" value="{{ old('jurusan') }}" required maxlength="150" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $isSmk ? 'NIS (Nomor Induk Siswa)' : 'NIM (Nomor Induk Mahasiswa)' }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="nis_nim" value="{{ old('nis_nim') }}" required maxlength="50" autocomplete="off" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <p class="text-[11px] text-slate-400 mt-1">Dipakai sebagai username login presensi saat magang, pastikan penulisannya benar.</p>
                        </div>
                    </div>
                </div>

                <!-- Pembimbing & Berkas -->
                <div class="border-t border-slate-200 pt-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-paperclip text-brand-blue"></i> Pembimbing &amp; Berkas
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $isSmk ? 'Nama Pembimbing' : 'Dosen Pembimbing' }} {!! $isSmk ? '<span class="text-rose-500">*</span>' : '<span class="text-slate-400 font-normal">(opsional)</span>' !!}</label>
                            <input type="text" name="pembimbing" value="{{ old('pembimbing') }}" maxlength="150" @required($isSmk) class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $isSmk ? 'No. HP Pembimbing' : 'No. WhatsApp Dosen Pembimbing' }} {!! $isSmk ? '<span class="text-rose-500">*</span>' : '<span class="text-slate-400 font-normal">(opsional)</span>' !!}</label>
                            <input type="tel" name="no_wa_pembimbing" value="{{ old('no_wa_pembimbing') }}" inputmode="tel" minlength="6" maxlength="20" placeholder="08xxxxxxxxxx (6-15 digit)" @required($isSmk) class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">CV / Portofolio {!! $isSmk ? '<span class="text-slate-400 font-normal">(opsional, PDF maks 5 MB)</span>' : '<span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(wajib, PDF maks 5 MB)</span>' !!}</label>
                        <input type="file" name="cv" accept="application/pdf" @required(! $isSmk) class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-semibold hover:file:bg-slate-200">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $isSmk ? 'Surat Pengajuan / Permohonan Sekolah' : 'Surat Pengajuan / Permohonan Universitas' }} <span class="text-slate-400 font-normal">(opsional, PDF maks 5 MB)</span></label>
                        <input type="file" name="surat_permohonan" accept="application/pdf" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-semibold hover:file:bg-slate-200">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="const f = document.getElementById('formLamaran'); if (f.reportValidity()) bukaPersyaratan(f);" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition">
                        Kirim Lamaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('modals')
    @include('lamaran._persyaratan')
@endpush
