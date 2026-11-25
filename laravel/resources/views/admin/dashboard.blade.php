@extends('layouts.admin-portal')

@section('title', 'Dashboard - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-soft p-6 rounded-3xl border border-slate-200/60 shadow-sm">
        <div>
            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-blue-100 text-brand-blue inline-block mb-2">
                ● OPERATIONAL DASHBOARD
            </span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang di Portal Admin</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau permohonan magang, verifikasi kehadiran harian, dan kuota lowongan aktif PT Mekar Armada Jaya.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <span class="text-xs font-bold text-slate-500 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm">
                <i class="fa-regular fa-calendar-check mr-1 text-brand-blue"></i> {{ now()->translatedFormat('d M Y') }}
            </span>
        </div>
    </div>

    <!-- STATISTIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Lowongan Dibuka</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stat['lowongan_dibuka'] }} Posisi</span>
                <span class="text-[11px] text-brand-blue font-bold flex items-center mt-1"><i class="fa-solid fa-briefcase mr-1"></i> Form Aktif</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center text-xl">
                <i class="fa-solid fa-briefcase"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menunggu Approval</span>
                <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $stat['lamaran_menunggu'] }} Lamaran</span>
                <span class="text-[11px] text-amber-600 font-bold flex items-center mt-1"><i class="fa-solid fa-clock mr-1"></i> Perlu verifikasi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-clock"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Anak Magang Aktif</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $stat['aktif_total'] }} Orang</span>
                <span class="text-[11px] text-slate-500 font-semibold mt-1 block">SMK: {{ $stat['aktif_smk'] }} | Mahasiswa: {{ $stat['aktif_kuliah'] }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hadir Hari Ini</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stat['hadir'] }} / {{ $stat['aktif_total'] }}</span>
                <span class="text-[11px] text-rose-500 font-semibold mt-1 block">{{ $stat['pending'] }} Laporan Pending</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-brand-purpleBlue flex items-center justify-center text-xl">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>
    </div>

    <!-- RECENT APPLICANTS & PRESENCE SUMMARY -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Lamaran Terbaru (Menunggu Decision)</h3>
                    <p class="text-xs text-slate-500">Berkas pendaftaran peserta yang membutuhkan persetujuan HRD.</p>
                </div>
                <a href="{{ route('admin.lamaran.index') }}" class="text-xs font-bold text-brand-blue hover:underline">Lihat Semua</a>
            </div>

            <div class="space-y-3">
                @forelse ($terbaru as $l)
                    @php $smk = $l->tipe_magang === 'smk'; @endphp
                    <div class="flex items-center justify-between bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 hover:bg-slate-100/80 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full {{ $smk ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-brand-blue' }} font-black text-xs flex items-center justify-center">{{ $smk ? 'SMK' : 'D3/S1' }}</div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">{{ $l->nama }}</h4>
                                <p class="text-[11px] text-slate-500">Posisi: <span class="font-semibold text-slate-700">{{ $l->nama_posisi }}</span> | {{ $l->asal_sekolah }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5"><i class="fa-regular fa-clock mr-1"></i> Tgl Daftar: {{ $l->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.lamaran.index', ['buka' => $l->id]) }}" class="px-3.5 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-brand-blue transition">Detail</a>
                    </div>
                @empty
                    <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500">
                        Belum ada lamaran yang menunggu keputusan.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Ringkasan Presensi Hari Ini</h3>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">{{ now()->translatedFormat('d M Y') }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-center">
                        <span class="block text-2xl font-black text-emerald-700">{{ $presensi['approved'] }}</span>
                        <span class="text-[11px] font-bold text-emerald-800">Hadir (Approved)</span>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-center">
                        <span class="block text-2xl font-black text-amber-700">{{ $presensi['izin'] }}</span>
                        <span class="text-[11px] font-bold text-amber-800">Izin / Sakit</span>
                    </div>
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-3 text-center">
                        <span class="block text-2xl font-black text-rose-700">{{ $presensi['alpha'] }}</span>
                        <span class="text-[11px] font-bold text-rose-800">Alpha</span>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-center">
                        <span class="block text-2xl font-black text-brand-blue">{{ $presensi['pending'] }}</span>
                        <span class="text-[11px] font-bold text-brand-blue">Pending ACC</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 leading-relaxed">
                    Terdapat <strong class="text-slate-800">{{ $presensi['pending'] }} laporan presensi harian &amp; kegiatan</strong> dari peserta magang yang memerlukan persetujuan.
                </p>
            </div>

            <a href="{{ route('admin.presensi.index') }}" class="w-full mt-6 py-3 bg-gradient-to-r from-brand-blue to-brand-purpleBlue hover:opacity-95 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                <span>Kelola Presensi &amp; Peserta Magang</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</div>
@endsection