@extends('layouts.admin-portal')

@section('title', 'Tambah Peserta Internal - Admin Portal PT Mekar Armada Jaya')

@section('content')
@php
    $inp  = 'w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue';
    $lbl  = 'block text-xs font-semibold text-slate-700 mb-1';
    $smk  = $pelamar->tipe_magang === 'smk';
    $jk   = $pelamar->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
@endphp
<div class="max-w-3xl mx-auto space-y-5">
    <div>
        <a href="{{ route('admin.lamaran.internal.create', ['q' => $pelamar->nis_nim]) }}" class="text-xs font-semibold text-slate-500 hover:text-brand-blue">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke pencarian
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-2">Tambah Peserta Internal</h1>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Profil peserta (diisi peserta sendiri)</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
            <div><span class="text-slate-400 block">Nama</span><strong class="text-slate-800">{{ $pelamar->nama }}</strong></div>
            <div><span class="text-slate-400 block">{{ $smk ? 'NIS' : 'NIM' }}</span><strong class="text-slate-800">{{ $pelamar->nis_nim }}</strong></div>
            <div><span class="text-slate-400 block">Jenjang</span><strong class="text-slate-800">{{ $smk ? 'Anak SMK' : 'Mahasiswa (D3/S1)' }}</strong></div>
            <div><span class="text-slate-400 block">{{ $smk ? 'Asal Sekolah' : 'Asal Kampus' }}</span><strong class="text-slate-800">{{ $pelamar->asal_sekolah }}</strong></div>
            <div><span class="text-slate-400 block">Jurusan</span><strong class="text-slate-800">{{ $pelamar->jurusan }}</strong></div>
            <div><span class="text-slate-400 block">Jenis Kelamin</span><strong class="text-slate-800">{{ $jk }}</strong></div>
            <div><span class="text-slate-400 block">{{ $smk ? 'Guru' : 'Dosen' }} Pembimbing</span><strong class="text-slate-800">{{ $pelamar->pembimbing ?: '-' }}</strong></div>
            <div class="col-span-2"><span class="text-slate-400 block">Email</span><strong class="text-slate-800">{{ $pelamar->email }}</strong></div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.lamaran.internal.store', $pelamar) }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        @csrf
        <div class="p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-900"><i class="fa-regular fa-calendar-check text-brand-blue mr-2"></i>Penempatan &amp; Periode</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label class="{{ $lbl }}">Departemen <span class="text-rose-500">*</span></label>
                    <select name="departemen_id" required class="{{ $inp }}">
                        <option value="">- Pilih departemen -</option>
                        @foreach ($departemen as $d)
                            <option value="{{ $d->id }}" @selected((string) old('departemen_id') === (string) $d->id)>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-1">
                    <label class="{{ $lbl }}">Tanggal Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai') }}" required class="{{ $inp }}">
                </div>
                <div class="sm:col-span-1">
                    <label class="{{ $lbl }}">Tanggal Selesai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tgl_selesai" value="{{ old('tgl_selesai') }}" required class="{{ $inp }}">
                </div>
            </div>
            <p class="text-[11px] text-slate-400">Jalur internal tidak dibatasi minimal durasi magang.</p>
        </div>

        <div class="p-6 bg-slate-50 rounded-b-2xl flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-[11px] text-slate-500 leading-relaxed max-w-xl">
                Peserta langsung berstatus <strong>Diterima</strong>. Akun presensi dibuat otomatis: login dengan NIS/NIM dan kata sandi yang sama dengan akun portal peserta.
            </p>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.lamaran.internal.create') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue shadow-sm hover:opacity-90 transition">Simpan &amp; Lanjut ke Surat Balasan</button>
            </div>
        </div>
    </form>
</div>
@endsection
