@extends('layouts.public')

@section('title', 'Ganti Kata Sandi - PT Mekar Armada Jaya')

@section('content')
<section class="py-12 bg-slate-100 min-h-[70vh]">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <h1 class="text-lg font-bold text-slate-900"><i class="fa-solid fa-key text-brand-blue mr-2"></i>Ganti Kata Sandi</h1>
                <p class="text-xs text-slate-500 mt-1">Halo, {{ $pelamar->nama_depan }}. Akun Dashboard Peserta Anda dibuat oleh HRD dengan kata sandi awal. Demi keamanan, buat kata sandi baru sebelum melanjutkan.</p>
            </div>

            <form method="POST" action="{{ route('pelamar.password.simpan') }}" class="p-6 space-y-4">
                @csrf

                @if (session('info'))
                    <div class="p-3 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200">{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="p-3 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                        <ul class="list-disc ml-4 space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 8 karakter.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <button type="submit" class="w-full px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition">Simpan Kata Sandi</button>
            </form>
        </div>
    </div>
</section>
@endsection
