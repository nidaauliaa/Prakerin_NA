@extends('layouts.public')

@section('title', 'Konfirmasi Lamaran - PT Mekar Armada Jaya')

@section('content')
@php
    $isSmk = $lowongan->tipe_magang === 'smk';
    $gj    = fn ($s) => $s ?: '-';
@endphp
<section class="py-12 bg-slate-100 min-h-[70vh]">
    <div class="max-w-2xl mx-auto px-4">
        <a href="{{ route('beranda') }}#lowongan" class="text-xs font-semibold text-slate-500 hover:text-brand-blue">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke daftar lowongan
        </a>

        <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
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
            </div>

            <form id="formKonfirmasi" method="POST" action="{{ route('lamaran.konfirmasi') }}" class="p-6 space-y-5">
                @csrf

                @if ($errors->any())
                    <div class="p-3.5 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                        <ul class="list-disc ml-4 space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-id-card text-brand-blue"></i> Data Diri (sudah tersimpan)
                    </h2>
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                        <div><span class="text-slate-400 block">Nama</span><strong class="text-slate-800">{{ $profil->nama }}</strong></div>
                        <div><span class="text-slate-400 block">{{ $profil->tipe_magang === 'smk' ? 'Asal Sekolah' : 'Asal Kampus' }}</span><strong class="text-slate-800">{{ $gj($profil->asal_sekolah) }}</strong></div>
                        <div><span class="text-slate-400 block">Jurusan</span><strong class="text-slate-800">{{ $gj($profil->jurusan) }}</strong></div>
                        <div><span class="text-slate-400 block">No. WhatsApp</span><strong class="text-slate-800">{{ $gj($profil->no_wa) }}</strong></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Data diri cukup diisi sekali di lamaran pertama. Lamaran ini memakai data yang sama. Detail lengkap ada di <a href="{{ route('pelamar.dashboard') }}" class="text-brand-blue font-semibold hover:underline">Dashboard Peserta</a>.</p>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="bukaPersyaratan(document.getElementById('formKonfirmasi'))" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition">
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
