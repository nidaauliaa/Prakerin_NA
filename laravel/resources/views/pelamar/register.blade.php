@extends('layouts.public')

@section('title', 'Daftar Pelamar - PT Mekar Armada Jaya')

@section('content')
<section class="py-16 bg-slate-100 min-h-[70vh]">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <div class="text-center mb-6">
                <span class="text-xs font-bold text-brand-blue uppercase tracking-widest">PORTAL PRAKERIN</span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Buat Akun Pelamar</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar sekali, lalu lamar posisi prakerin / magang.</p>
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

            <form method="POST" action="{{ route('pelamar.register.store') }}" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="150" autofocus class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="150" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(min. 8 karakter)</span></label>
                    <input type="password" name="password" required minlength="8" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Kata Sandi <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <button type="submit" class="w-full py-2.5 bg-brand-blue hover:bg-brand-darkBlue text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                    Daftar &amp; Lanjutkan
                </button>
            </form>

            <p class="text-xs text-center text-slate-500 mt-6">
                Sudah punya akun?
                <a href="{{ route('pelamar.login') }}" class="font-bold text-brand-blue hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</section>
@endsection