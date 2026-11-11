@php
    // Menu "Peserta" mengelompokkan 5 halaman. Grup terbuka otomatis kalau halaman aktif ada di dalamnya.
    $grupPeserta = [
        ['Presensi',      route('admin.presensi.index'),       request()->routeIs('admin.presensi.*') && ! request()->routeIs('admin.presensi.export*'), 0],
        ['Peserta Aktif', route('admin.aktif.index'),          request()->routeIs('admin.aktif.*', 'admin.peserta.create', 'admin.peserta.edit', 'admin.peserta.update'), 0],
        ['Akun Peserta',  route('admin.akun.index'),           request()->routeIs('admin.akun.*'),                                      (int) ($jumlahResetBaru ?? 0)],
        ['Tugas',         route('admin.tugas.index'),          request()->routeIs('admin.tugas.*'),                                     0],
        ['Arsip',         route('admin.arsip.index'),          request()->routeIs('admin.arsip.*'),                                     0],
    ];
    $grupAktif = collect($grupPeserta)->contains(fn ($m) => $m[2]);
    $grupBadge = collect($grupPeserta)->sum(fn ($m) => $m[3]);
    $gpsAktif = request()->routeIs('admin.lokasi-curiga.*');
    $gpsBadge = (int) ($jumlahLokasiCuriga ?? 0);
    $zonaAktif = request()->routeIs('admin.zona.*');
    $kalenderAktif = request()->routeIs('admin.kalender.*');
    $importAktif = request()->routeIs('admin.peserta.import*');
    $exportAktif = request()->routeIs('admin.presensi.export*');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin Presensi') — New Armada Group</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{brand:{blue:'#3b82f6',purpleBlue:'#6366f1',red:'#e11d48',dark:'#0f172a',lightBg:'#f8fafc'}}}}}</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
  // Status sidebar diingat antar halaman (bawaan: tertutup, hanya ikon garis tiga). Di layar kecil selalu mulai tertutup.
  try { if (window.innerWidth >= 1024 && localStorage.getItem('admSidebar') === '1') document.documentElement.classList.add('sb-open'); } catch (e) {}
  function toggleSidebar(buka) {
    var h = document.documentElement;
    var jadi = (typeof buka === 'boolean') ? buka : !h.classList.contains('sb-open');
    h.classList.toggle('sb-open', jadi);
    try { localStorage.setItem('admSidebar', jadi ? '1' : '0'); } catch (e) {}
  }
</script>
<style>
[x-cloak]{display:none!important}
#sidebar{display:none}
html.sb-open #sidebar{display:flex}
#sbBackdrop{display:none}
@media (max-width:1023px){html.sb-open #sbBackdrop{display:block}}
</style>
</head>
<body class="bg-white font-sans text-slate-800 min-h-screen flex flex-col">

@include('admin.partials.topnav', ['denganSidebar' => true])

<div class="flex flex-1 min-h-0">

<div id="sbBackdrop" onclick="toggleSidebar(false)" class="fixed inset-0 top-20 bg-slate-900/40 z-30"></div>

<aside id="sidebar" class="fixed lg:sticky top-20 left-0 z-40 w-64 shrink-0 h-[calc(100vh-5rem)] self-start flex-col bg-slate-50 border-r border-slate-200">
<nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-[13px] font-medium">

{{-- Menu: Peserta (dropdown) --}}
<div x-data="{ buka: {{ $grupAktif ? 'true' : 'false' }} }">
<button type="button" @click="buka = !buka"
        class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-left {{ $grupAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
<span class="flex-1">Peserta</span>
@if($grupBadge > 0)<span x-show="!buka" class="rounded-full bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5">{{ $grupBadge }}</span>@endif
<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0 text-slate-400 transition-transform" :class="buka ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div x-show="buka" x-transition.opacity.duration.150ms class="mt-1 space-y-0.5">
@foreach($grupPeserta as [$label, $url, $aktif, $badge])
<a href="{{ $url }}" class="flex items-center justify-between rounded-lg pl-10 pr-3 py-1.5 {{ $aktif ? 'bg-white shadow-sm ring-1 ring-slate-200 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-200/60' }}">
<span>{{ $label }}</span>
@if($badge > 0)<span class="rounded-full bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5">{{ $badge }}</span>@endif
</a>
@endforeach
</div>
</div>

{{-- Menu lain --}}
<a href="{{ route('admin.lokasi-curiga.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $gpsAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"/></svg>
<span class="flex-1">Abnormal GPS</span>
@if($gpsBadge > 0)<span class="rounded-full bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5">{{ $gpsBadge }}</span>@endif
</a>
<a href="{{ route('admin.zona.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $zonaAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
<span>Zona Absen</span>
</a>
<a href="{{ route('admin.kalender.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $kalenderAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
<span>Kalender Kerja</span>
</a>
<a href="{{ route('admin.peserta.import.form') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $importAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
<span>Import</span>
</a>
<a href="{{ route('admin.presensi.export.form') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $exportAktif ? 'bg-slate-200/70 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-200/60' }}">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
<span>Export</span>
</a>
</nav>

<div class="border-t border-slate-200 p-3">
<button type="button" onclick="toggleSidebar(false)" class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 hover:bg-slate-200/60">
<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 4v16M14 12H4M8 8l-4 4 4 4"/></svg>
<span>Collapse sidebar</span>
</button>
</div>
</aside>

<div class="flex-1 min-w-0 flex flex-col">
<main class="flex-1 max-w-7xl w-full mx-auto px-6 py-8">
@if(session('success'))<div class="mb-4 rounded-lg bg-green-50 text-green-700 text-sm px-4 py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-lg bg-red-50 text-red-700 text-sm px-4 py-2">{{ session('error') }}</div>@endif
@yield('content')
</main>
</div>

</div>

<footer class="bg-slate-900 text-slate-400 text-xs">
<div class="px-6 h-12 flex items-center justify-between">
<span><b class="text-red-500">new</b> <b class="text-blue-400">armada</b> | Portal Prakerin</span>
<span>© {{ date('Y') }} PT Mekar Armada Jaya</span>
</div>
</footer>
<script>
  // Tombol Back/Forward kadang memulihkan halaman dari memori browser (bfcache). Paksa muat ulang dari server,
  // jadi setelah logout halaman lama tidak muncul lagi (server akan mengarahkan ke login).
  window.addEventListener('pageshow', function (e) { if (e.persisted) window.location.reload(); });
  // Esc menutup sidebar di layar kecil.
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && window.innerWidth < 1024) toggleSidebar(false); });
</script>
</body>
</html>