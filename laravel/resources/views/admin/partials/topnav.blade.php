@php
    $adm = (config('auth.guards.admin') ? \Illuminate\Support\Facades\Auth::guard('admin')->user() : null)
        ?? \Illuminate\Support\Facades\Auth::user();
    $admNama  = $adm->nama ?? $adm->name ?? 'Administrator';
    $admEmail = $adm->email ?? '';
    $inisial  = strtoupper(mb_substr($admNama, 0, 2));
    $lamaranPending = \App\Models\Lamaran::where('status', 'menunggu')->count();

    $aktif = [
        'dashboard' => request()->routeIs('admin.dashboard'),
        'lamaran'   => request()->routeIs('admin.lamaran.*'),
        'lowongan'  => request()->routeIs('admin.lowongan.*'),
    ];
    // Semua halaman absensi (presensi, peserta, arsip, zona, kalender, dst.) = menu "Peserta & Presensi"
    $aktif['peserta'] = request()->routeIs('admin.*') && ! in_array(true, $aktif, true);

    $on  = 'px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-purpleBlue text-white font-bold transition shadow-sm flex items-center gap-2';
    $off = 'px-4 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition flex items-center gap-2';
    $mOn  = 'px-3 py-1.5 rounded-lg bg-brand-blue text-white font-bold whitespace-nowrap';
    $mOff = 'px-3 py-1.5 rounded-lg text-slate-600 bg-slate-100 whitespace-nowrap';
@endphp

<!-- HEADER DASHBOARD ADMIN (MATCHED WITH LANDING PAGE BRANDING) -->
<header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

        <div class="flex items-center gap-3">
            @if (! empty($denganSidebar))
                {{-- Hanya di halaman absensi: buka/tutup sidebar (fungsi toggleSidebar ada di layouts.admin) --}}
                <button type="button" onclick="toggleSidebar()" aria-label="Buka atau tutup menu" title="Menu"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            @endif

            <!-- BRAND LOGO -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <div class="flex flex-col">
                    <span class="text-xl sm:text-2xl font-black tracking-tight text-brand-red leading-none">
                        new <span class="text-brand-blue">armada</span> <span class="text-slate-900 font-extrabold">group</span>
                    </span>
                    <span class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase mt-1">PT MEKAR ARMADA JAYA</span>
                </div>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-brand-blue border border-blue-200">
                    Admin Portal
                </span>
            </a>
        </div>

        <!-- NAVIGATION TABS -->
        <nav class="hidden md:flex items-center space-x-1 font-semibold text-xs text-slate-600">
            <a href="{{ route('admin.dashboard') }}" class="{{ $aktif['dashboard'] ? $on : $off }}">
                <i class="fa-solid fa-chart-line text-xs"></i> Dashboard
            </a>
            <a href="{{ route('admin.lamaran.index') }}" class="{{ $aktif['lamaran'] ? $on : $off }}">
                <i class="fa-solid fa-id-card text-xs"></i> Lamaran Masuk
                @if ($lamaranPending > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $lamaranPending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.lowongan.index') }}" class="{{ $aktif['lowongan'] ? $on : $off }}">
                <i class="fa-solid fa-briefcase text-xs"></i> Kelola Lowongan
            </a>
            <a href="{{ route('admin.presensi.index') }}" class="{{ $aktif['peserta'] ? $on : $off }}">
                <i class="fa-solid fa-users-gear text-xs"></i> Peserta &amp; Presensi
            </a>
        </nav>

        <!-- USER PROFILE & LOGOUT -->
        <div class="flex items-center space-x-3">
            <div class="hidden sm:flex items-center space-x-3 border-r border-slate-200 pr-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-brand-blue to-brand-purpleBlue text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    {{ $inisial }}
                </div>
                <div class="text-left text-xs">
                    <p class="font-bold text-slate-900 leading-tight">{{ $admNama }}</p>
                    <p class="text-[11px] text-slate-400">{{ $admEmail }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari Dashboard Admin HRD?')">
                @csrf
                <button type="submit" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Log Out">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- MOBILE NAVIGATION -->
<div class="md:hidden bg-white border-b border-slate-200 px-4 py-2 flex space-x-2 overflow-x-auto custom-scrollbar text-xs">
    <a href="{{ route('admin.dashboard') }}" class="{{ $aktif['dashboard'] ? $mOn : $mOff }}">Dashboard</a>
    <a href="{{ route('admin.lamaran.index') }}" class="{{ $aktif['lamaran'] ? $mOn : $mOff }}">Lamaran{{ $lamaranPending > 0 ? " ($lamaranPending)" : '' }}</a>
    <a href="{{ route('admin.lowongan.index') }}" class="{{ $aktif['lowongan'] ? $mOn : $mOff }}">Lowongan</a>
    <a href="{{ route('admin.presensi.index') }}" class="{{ $aktif['peserta'] ? $mOn : $mOff }}">Peserta &amp; Presensi</a>
</div>