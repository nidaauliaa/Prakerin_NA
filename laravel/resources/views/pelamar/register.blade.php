@extends('layouts.pelamar-auth')

@section('title', 'Daftar Pelamar')
@section('ikon', 'fa-user-plus')
@section('judul', 'Buat Akun Pelamar')
@section('subjudul', 'Daftar sekali, lalu lamar posisi prakerin / magang.')

@section('form')
    <form method="POST" action="{{ route('pelamar.register.store') }}" class="space-y-3.5">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="150" placeholder="Nama lengkap sesuai identitas"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="150" placeholder="nama@email.com"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(min. 8 karakter)</span></label>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" name="password" required minlength="8" placeholder="••••••••"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ulangi Kata Sandi <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-shield-halved absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" name="password_confirmation" required minlength="8" placeholder="••••••••"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 bg-brand-blue hover:bg-brand-darkBlue text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/20 transition">
            Daftar &amp; Lanjutkan <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
        </button>
    </form>
@endsection

@section('tautan')
    <p class="text-xs text-slate-500">
        Sudah punya akun?
        <a href="{{ route('pelamar.login') }}" class="font-bold text-brand-red hover:underline">Masuk</a>
    </p>
@endsection
