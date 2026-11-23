@extends('layouts.admin-portal')

@section('title', 'Tambah Peserta Internal - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div>
        <a href="{{ route('admin.lamaran.index') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-blue">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Kelola Lamaran Masuk
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-2">Tambah Peserta Internal</h1>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Untuk peserta yang masuk lewat surat permohonan (tanpa melamar di portal). Peserta harus <strong>membuat akun dan melengkapi profil</strong> di portal lebih dulu.
            Cari berdasarkan nama atau NIS/NIM, lalu pilih departemen dan periode magang.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.lamaran.internal.create') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <label class="block text-xs font-semibold text-slate-700 mb-1">Cari peserta (nama atau NIS/NIM)</label>
        <div class="flex gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="q" value="{{ $q }}" autofocus placeholder="mis. Budi Santoso atau 2021110045" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue shadow-sm hover:opacity-90 transition">Cari</button>
        </div>
    </form>

    @if ($q !== '' && $hasil->isEmpty())
        <div class="p-4 rounded-2xl text-xs bg-amber-50 text-amber-900 border border-amber-200 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-amber-500 text-base mt-0.5"></i>
            <div class="leading-relaxed">
                <p class="font-bold text-sm">Peserta tidak ditemukan</p>
                Pastikan peserta sudah punya akun dan sudah melengkapi profil (termasuk NIS/NIM) terlebih dahulu, lalu cari lagi.
            </div>
        </div>
    @endif

    @if ($hasil->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
            @foreach ($hasil as $p)
                <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900">{{ $p->nama }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            NIS/NIM: <strong class="text-slate-700">{{ $p->nis_nim ?: 'belum diisi' }}</strong>
                            &middot; {{ $p->email }}
                        </p>
                        @if ($p->asal_sekolah || $p->jurusan)
                            <p class="text-[11px] text-slate-400">{{ $p->asal_sekolah }} @if($p->jurusan)&middot; {{ $p->jurusan }}@endif</p>
                        @endif
                    </div>

                    @if ($p->status_internal === 'siap')
                        <a href="{{ route('admin.lamaran.internal.form', $p) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition flex items-center gap-2">
                            Pilih <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @elseif ($p->status_internal === 'diterima')
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-500">Sudah menjadi peserta</span>
                    @else
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800" title="Minta peserta melengkapi profil di Dashboard Peserta">Profil belum lengkap</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
