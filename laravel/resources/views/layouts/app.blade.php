<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('judul', 'Presensi Prakerin')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--b1:#3b5bf0;--b2:#8b5cf6;--nv:#0f1420;--gy:#64748b;--bd:#e3e6ef;--hj:#16a34a;--mr:#dc2626;--kn:#d97706;--bi:#2563eb}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,-apple-system,Segoe UI,Arial,sans-serif;color:var(--nv);background:#f6f7fb}
a{color:inherit}
.topnav{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:18px;padding:8px 22px;background:#fff;border-bottom:1px solid var(--bd)}
.topnav .brand{display:flex;align-items:center;gap:10px;justify-self:start;text-decoration:none}
.topnav .brand .chip{background:#fff;border-radius:12px;padding:4px 7px;display:flex;box-shadow:0 2px 8px rgba(30,41,89,.12);border:1px solid var(--bd)}
.topnav .brand .chip img{height:34px;width:auto;display:block}
.topnav .brand b{display:block;font-size:14px;line-height:1.2}
.topnav .brand small{display:block;font-size:9.5px;color:var(--b1);font-weight:700;letter-spacing:.2px}
@media(max-width:520px){.topnav{grid-template-columns:auto 1fr auto;gap:10px;padding:8px 14px}.topnav .brand small{display:none}.topnav .brand b{font-size:13px}}
.nav-desktop{display:flex;justify-content:center;gap:18px;font-size:13.5px;font-weight:600;color:var(--gy)}
.nav-desktop a.aktif{color:var(--b1)}
.btn-keluar{background:#f1f3f9;border:0;padding:8px 14px;border-radius:10px;font:600 12.5px Inter,sans-serif;cursor:pointer;color:var(--nv)}
main{max-width:560px;margin:0 auto;padding:20px 16px}
@media(min-width:768px){
  main{max-width:620px;padding-top:30px}
}
.card{background:#fff;border-radius:18px;padding:18px;box-shadow:0 10px 24px -14px rgba(30,41,89,.18);margin-bottom:16px}
h1{font-size:21px;margin:0 0 4px}.sub{font-size:13px;color:var(--gy);margin:0 0 18px}
.btn{display:block;width:100%;padding:13px;border:0;border-radius:14px;background:linear-gradient(100deg,var(--b1),var(--b2));color:#fff;font:700 14.5px Inter,sans-serif;cursor:pointer;text-align:center;text-decoration:none}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn-outline{display:block;width:100%;padding:13px;border:1.5px solid var(--bd);border-radius:14px;background:#fff;color:var(--nv);font:700 14.5px Inter,sans-serif;text-align:center;text-decoration:none;margin-top:10px}
.btn small,.btn-outline small{display:block;font-weight:500;font-size:11.5px;opacity:.85;margin-top:2px}
</style>
@stack('css')
</head>
<body>
<header class="topnav">
  <a href="{{ route('presensi.index') }}" class="brand">
    <span class="chip"><img src="{{ asset('images/logo-newarmada.png') }}" alt="New Armada"></span>
    <span><b>New Armada</b><small>PT MEKAR ARMADA JAYA</small></span>
  </a>
  <nav class="nav-desktop">
    <a href="{{ route('presensi.index') }}" class="{{ request()->routeIs('presensi.*') ? 'aktif' : '' }}">Presensi</a>
    <a href="{{ route('laporan.riwayat') }}" class="{{ request()->routeIs('laporan.*') ? 'aktif' : '' }}">Riwayat Laporan</a>
  </nav>
  <form method="POST" action="{{ route('logout') }}" style="justify-self:end;margin:0">
    @csrf
    <button class="btn-keluar" type="submit">Keluar</button>
  </form>
</header>

<main>
  @if (session('sukses'))<div class="card" style="background:#ecfdf3;color:#0f7a3a">{{ session('sukses') }}</div>@endif
  @if (session('info'))<div class="card" style="background:#eef2ff;color:var(--b1)">{{ session('info') }}</div>@endif
  @yield('konten')
</main>

@stack('js')
<script>
  // Tombol Back/Forward kadang memulihkan halaman dari memori browser (bfcache). Paksa muat ulang dari server,
  // jadi setelah logout halaman lama tidak muncul lagi (server akan mengarahkan ke login).
  window.addEventListener('pageshow', function (e) { if (e.persisted) window.location.reload(); });
</script>
</body>
</html>