@extends('layouts.pelamar-auth')

@section('title', 'Masuk Pelamar')
@section('ikon', 'fa-right-to-bracket')
@section('judul', 'Masuk ke Akun Anda')
@section('subjudul', 'Masuk untuk melanjutkan lamaran prakerin / magang.')

@section('form')
    <form method="POST" action="{{ route('pelamar.login.store') }}" class="space-y-3.5">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition">
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-600">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="ingat" value="1" class="rounded border-slate-300 text-brand-blue">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="w-full py-2.5 bg-brand-blue hover:bg-brand-darkBlue text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/20 transition">
            Masuk <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
        </button>
    </form>
@endsection

@section('tautan')
    <p class="text-xs text-slate-500">
        Belum punya akun?
        <a href="{{ route('pelamar.register') }}" class="font-bold text-brand-red hover:underline">Daftar sekarang</a>
    </p>
@endsection
