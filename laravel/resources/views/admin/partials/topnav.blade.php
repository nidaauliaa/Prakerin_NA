@php
    $adm = (config('auth.guards.admin') ? \Illuminate\Support\Facades\Auth::guard('admin')->user() : null)
        ?? \Illuminate\Support\Facades\Auth::user();
    $admNama  = $adm->nama ?? $adm->name ?? 'Administrator';
    $admEmail = $adm->email ?? '';
    $inisial  = strtoupper(mb_substr($admNama, 0, 2));
    $admFoto  = ! empty($adm->foto_profil) ? route('admin.profil.foto', ['v' => $adm->updated_at?->timestamp]) : null;
    $lamaranPending = \App\Models\Lamaran::where('status', 'menunggu')->count();

    $aktif = [
        'dashboard' => request()->routeIs('admin.dashboard'),
        'lamaran'   => request()->routeIs('admin.lamaran.*'),
        'lowongan'  => request()->routeIs('admin.lowongan.*'),
        'penilaian' => request()->routeIs('admin.penilaian.*', 'admin.surat-keterangan.*'),
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
            <a href="{{ route('admin.penilaian.index') }}" class="{{ $aktif['penilaian'] ? $on : $off }}">
                <i class="fa-solid fa-clipboard-check text-xs"></i> Penilaian
            </a>
            <a href="{{ route('admin.presensi.index') }}" class="{{ $aktif['peserta'] ? $on : $off }}">
                <i class="fa-solid fa-users-gear text-xs"></i> Peserta &amp; Presensi
            </a>
        </nav>

        <!-- FOTO PROFIL + MENU (nama lengkap, ubah foto, keluar) -->
        <div class="relative" id="menuAdminWrap">
            <button type="button" id="btnMenuAdmin" aria-haspopup="true" aria-expanded="false" title="Menu akun"
                    class="flex items-center space-x-3 rounded-xl px-2 py-1.5 hover:bg-slate-50 transition focus:outline-none">
                <span class="w-9 h-9 shrink-0 rounded-full overflow-hidden shadow-sm bg-gradient-to-tr from-brand-blue to-brand-purpleBlue text-white flex items-center justify-center font-bold text-xs">
                    @if ($admFoto)
                        <img src="{{ $admFoto }}" alt="Foto profil" class="w-full h-full object-cover">
                    @else
                        {{ $inisial }}
                    @endif
                </span>
                <span class="hidden sm:block text-left text-xs">
                    <span class="block font-bold text-slate-900 leading-tight">{{ $admNama }}</span>
                    <span class="block text-[11px] text-slate-400">{{ $admEmail }}</span>
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
            </button>

            <div id="menuAdmin" class="hidden absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-bold text-slate-900 break-words">{{ $admNama }}</p>
                    <p class="text-[11px] text-slate-400 break-all">{{ $admEmail }}</p>
                </div>

                <form method="POST" action="{{ route('admin.profil.foto.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="foto" id="inputFotoAdmin" accept="image/png,image/jpeg" class="hidden" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('inputFotoAdmin').click()"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                        <i class="fa-solid fa-camera w-4 text-center text-slate-400"></i> Ubah Foto Profil
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari Dashboard Admin HRD?')">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Keluar (Logout)
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- MOBILE NAVIGATION -->
<div class="md:hidden bg-white border-b border-slate-200 px-4 py-2 flex space-x-2 overflow-x-auto custom-scrollbar text-xs">
    <a href="{{ route('admin.dashboard') }}" class="{{ $aktif['dashboard'] ? $mOn : $mOff }}">Dashboard</a>
    <a href="{{ route('admin.lamaran.index') }}" class="{{ $aktif['lamaran'] ? $mOn : $mOff }}">Lamaran{{ $lamaranPending > 0 ? " ($lamaranPending)" : '' }}</a>
    <a href="{{ route('admin.lowongan.index') }}" class="{{ $aktif['lowongan'] ? $mOn : $mOff }}">Lowongan</a>
    <a href="{{ route('admin.penilaian.index') }}" class="{{ $aktif['penilaian'] ? $mOn : $mOff }}">Penilaian</a>
    <a href="{{ route('admin.presensi.index') }}" class="{{ $aktif['peserta'] ? $mOn : $mOff }}">Peserta &amp; Presensi</a>
</div>

@if (session('sukses_foto') || session('galat_foto'))
    @php $fotoOk = (bool) session('sukses_foto'); @endphp
    <div id="toastFotoAdmin" class="fixed top-24 right-4 z-[60] max-w-sm w-[calc(100%-2rem)] bg-white border {{ $fotoOk ? 'border-emerald-200' : 'border-rose-200' }} shadow-xl rounded-2xl p-4 flex items-start space-x-3">
        <div class="w-9 h-9 shrink-0 rounded-full {{ $fotoOk ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center">
            <i class="fa-solid {{ $fotoOk ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
        </div>
        <div class="text-xs text-slate-600 leading-relaxed flex-1 self-center">{{ session('sukses_foto') ?? session('galat_foto') }}</div>
        <button onclick="document.getElementById('toastFotoAdmin').remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif

<script>
    // Menu foto profil admin: buka/tutup saat diklik, tutup saat klik di luar atau tekan Esc
    (function () {
        const btn = document.getElementById('btnMenuAdmin');
        const menu = document.getElementById('menuAdmin');
        if (btn && menu) {
            const tutup = () => { menu.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); };
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const buka = menu.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
            });
            document.addEventListener('click', (e) => { if (!menu.contains(e.target)) tutup(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
        }
        const toast = document.getElementById('toastFotoAdmin');
        if (toast) setTimeout(() => toast.remove(), 5000);
    })();
</script>
