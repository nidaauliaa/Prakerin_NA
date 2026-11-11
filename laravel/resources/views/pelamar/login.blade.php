@extends('layouts.public')

@section('title', 'Masuk Pelamar - PT Mekar Armada Jaya')

@section('content')
<section class="py-16 bg-slate-100 min-h-[70vh]">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <div class="text-center mb-6">
                <span class="text-xs font-bold text-brand-blue uppercase tracking-widest">PORTAL PRAKERIN</span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Masuk ke Akun Anda</h1>
                <p class="text-xs text-slate-500 mt-1">Masuk untuk melanjutkan lamaran prakerin / magang.</p>
            </div>

            @if (session('info'))
                <div class="mb-6 p-3.5 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200 flex items-start space-x-2.5">
                    <i class="fa-solid fa-circle-info text-amber-600 text-base mt-0.5"></i>
                    <div class="leading-relaxed">{{ session('info') }}</div>
                </div>
            @endif

            @if ($draft)
                <div class="mb-6 p-3.5 rounded-xl text-xs bg-blue-50 text-blue-900 border border-blue-200 flex items-start space-x-2.5">
                    <i class="fa-regular fa-calendar-check text-brand-blue text-base mt-0.5"></i>
                    <div class="leading-relaxed">
                        Lamaran Anda: <strong>{{ $draft['posisi'] }}</strong><br>
                        Periode: <strong>{{ $draft['mulai'] }} s/d {{ $draft['selesai'] }}</strong>
                    </div>
                </div>
            @endif

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

            <form method="POST" action="{{ route('pelamar.login.store') }}" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <label class="flex items-center space-x-2 text-xs text-slate-600">
                    <input type="checkbox" name="ingat" value="1" class="rounded border-slate-300 text-brand-blue">
                    <span>Ingat saya</span>
                </label>
                <button type="submit" class="w-full py-2.5 bg-brand-blue hover:bg-brand-darkBlue text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                    Masuk
                </button>
            </form>

            <p class="text-xs text-center text-slate-500 mt-6">
                Belum punya akun?
                <a href="{{ route('pelamar.register') }}" class="font-bold text-brand-blue hover:underline">Daftar sekarang</a>
            </p>
            <p class="text-[11px] text-center text-slate-400 mt-3">
                Peserta magang aktif? Masuk lewat
                <a href="{{ route('login') }}" class="underline hover:text-slate-600">login absensi</a>.
            </p>
        </div>
    </div>
</section>
@endsection