<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Prakerin & Magang Industri - PT Mekar Armada Jaya')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#2563eb',
                            darkBlue: '#1d4ed8',
                            red: '#e11d48',
                            navy: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans antialiased">

    <!-- HEADER / NAVIGATION -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                <div class="flex flex-col">
                    <span class="text-2xl font-black tracking-tight text-brand-red leading-none">new <span class="text-brand-blue">armada</span></span>
                    <span class="text-[10px] font-bold text-slate-500 tracking-widest uppercase mt-1">PT Mekar Armada Jaya</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 font-medium text-sm text-slate-600">
                <a href="{{ route('beranda') }}#beranda" class="hover:text-brand-blue transition-colors py-2">Beranda</a>
                <a href="{{ route('beranda') }}#profil" class="hover:text-brand-blue transition-colors py-2">Profil Perusahaan</a>
                <a href="{{ route('beranda') }}#lowongan" class="hover:text-brand-blue transition-colors py-2">Persyaratan & Lowongan</a>
                <a href="{{ route('beranda') }}#galeri" class="hover:text-brand-blue transition-colors py-2">Galeri Magang</a>
                <a href="{{ route('beranda') }}#kontak" class="hover:text-brand-blue transition-colors py-2">Kontak</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center space-x-3">
                @auth('pelamar')
                    @php
                        $plm     = auth('pelamar')->user();
                        $plmFoto = $plm->fotoPath() ? route('pelamar.foto', ['v' => $plm->updated_at?->timestamp]) : null;
                    @endphp
                    <span class="hidden xl:inline text-sm font-medium text-slate-600">Halo, {{ $plm->nama_depan }}</span>

                    <a href="{{ route('pelamar.dashboard') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-full text-sm font-semibold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-sm shadow-blue-500/20 transition">
                        <i class="fa-solid fa-gauge-high text-xs"></i>
                        <span class="hidden sm:inline">Dashboard Peserta</span><span class="sm:hidden">Dashboard</span>
                    </a>

                    <!-- Foto profil + menu (nama lengkap, ubah foto, keluar) -->
                    <div class="relative" id="menuProfilWrap">
                        <button type="button" id="btnMenuProfil" aria-haspopup="true" aria-expanded="false" title="Menu akun"
                                class="block w-11 h-11 rounded-full overflow-hidden ring-2 ring-white shadow-md hover:ring-blue-200 transition focus:outline-none focus:ring-blue-300">
                            @if ($plmFoto)
                                <img src="{{ $plmFoto }}" alt="Foto profil {{ $plm->nama_depan }}" class="w-full h-full object-cover">
                            @else
                                <span class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-brand-blue to-indigo-500 text-white font-bold">{{ $plm->inisial }}</span>
                            @endif
                        </button>

                        <div id="menuProfil" class="hidden absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-bold text-slate-900 break-words">{{ $plm->nama }}</p>
                                <p class="text-[11px] text-slate-400 break-all">{{ $plm->email }}</p>
                            </div>

                            <form id="formFotoProfil" method="POST" action="{{ route('pelamar.foto.update') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="foto" id="inputFotoProfil" accept="image/png,image/jpeg" class="hidden" onchange="this.form.submit()">
                                <button type="button" onclick="document.getElementById('inputFotoProfil').click()"
                                        class="w-full text-left px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                                    <i class="fa-solid fa-camera w-4 text-center text-slate-400"></i> Ubah Foto Profil
                                </button>
                            </form>

                            <form method="POST" action="{{ route('pelamar.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-3">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('pelamar.login') }}" class="px-5 py-2.5 rounded-full text-sm font-semibold text-brand-blue border border-brand-blue hover:bg-blue-50 transition">
                        Sign In / Log In
                    </a>
                @endauth
            </div>
        </div>
    </header>

@if (session('sukses_foto') || session('galat_foto'))
    @php $fotoOk = (bool) session('sukses_foto'); @endphp
    <div id="toastFoto" class="fixed top-24 right-4 z-[60] max-w-sm w-[calc(100%-2rem)] bg-white border {{ $fotoOk ? 'border-emerald-200' : 'border-rose-200' }} shadow-xl rounded-2xl p-4 flex items-start space-x-3">
        <div class="w-9 h-9 shrink-0 rounded-full {{ $fotoOk ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center">
            <i class="fa-solid {{ $fotoOk ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
        </div>
        <div class="text-xs text-slate-600 leading-relaxed flex-1 self-center">{{ session('sukses_foto') ?? session('galat_foto') }}</div>
        <button onclick="document.getElementById('toastFoto').remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif

@yield('content')

    <!-- FOOTER (Gambar 4 Footer) -->
    <footer id="kontak" class="bg-[#0f172a] text-slate-300 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-800">
                <!-- Brand Info -->
                <div class="md:col-span-4 space-y-3">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-brand-red leading-none">new <span class="text-brand-blue">armada</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Mekar Armada Jaya — mempelopori inovasi industri karoseri dan komponen otomotif Indonesia.
                    </p>
                </div>

                <!-- Kontak -->
                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Kontak</h4>
                    <p class="text-sm text-slate-400">+6281217609906</p>
                    <p class="text-sm text-slate-400">prakerin.maj@gmail.com</p>
                </div>

                <!-- Alamat -->
                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Alamat</h4>
                    <p class="text-sm text-slate-400">
                        Jl. Mayjen Bambang Soegeng No.7, Kedungdowo, Mertoyudan, Kec. Mertoyudan, Kabupaten Magelang, Jawa Tengah 56172.
                    </p>
                </div>

                <!-- Tautan -->
                <div class="md:col-span-2 space-y-2">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Tautan</h4>
                    <ul class="space-y-1.5 text-sm text-slate-400">
                        <li><a href="{{ route('beranda') }}#beranda" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('beranda') }}#lowongan" class="hover:text-white transition">Lowongan</a></li>
                        <li><a href="{{ route('beranda') }}#profil" class="hover:text-white transition">Tentang</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; 2026 New Armada. Seluruh hak cipta dilindungi.</p>
                <p>Program Magang & Prakerin Industri</p>
            </div>
        </div>
    </footer>

@stack('modals')
<script>
    // Menu foto profil: buka/tutup saat foto diklik, tutup saat klik di luar atau tekan Esc
    (function () {
        const btn = document.getElementById('btnMenuProfil');
        const menu = document.getElementById('menuProfil');
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
        const toast = document.getElementById('toastFoto');
        if (toast) setTimeout(() => toast.remove(), 5000);
    })();
</script>
@stack('scripts')
</body>
</html>