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
                    <span class="text-2xl font-black tracking-tight text-brand-red leading-none">new <span class="text-brand-blue">armada</span> group</span>
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
                    <span class="hidden sm:inline text-sm font-medium text-slate-600">Halo, {{ auth('pelamar')->user()->nama }}</span>
                    <form method="POST" action="{{ route('pelamar.logout') }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-full text-sm font-semibold text-brand-blue border border-brand-blue hover:bg-blue-50 transition">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('pelamar.login') }}" class="px-5 py-2.5 rounded-full text-sm font-semibold text-brand-blue border border-brand-blue hover:bg-blue-50 transition">
                        Sign In / Log In
                    </a>
                @endauth
            </div>
        </div>
    </header>

@yield('content')

    <!-- FOOTER (Gambar 4 Footer) -->
    <footer id="kontak" class="bg-[#0f172a] text-slate-300 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-800">
                <!-- Brand Info -->
                <div class="md:col-span-4 space-y-3">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-brand-red leading-none">new <span class="text-brand-blue">armada</span> group</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Bagian dari PT Mekar Armada Investama — membangun masa depan industri manufaktur Indonesia.
                    </p>
                </div>

                <!-- Kontak -->
                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Kontak</h4>
                    <p class="text-sm text-slate-400">(024) 123-45678</p>
                    <p class="text-sm text-slate-400">magang@newarmadagroup.co.id</p>
                </div>

                <!-- Alamat -->
                <div class="md:col-span-3 space-y-2">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Alamat</h4>
                    <p class="text-sm text-slate-400">
                        Jl. Raya Semarang – Kendal KM 12, Ungaran, Kabupaten Semarang, Jawa Tengah, Indonesia
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
                <p>&copy; 2026 New Armada Group. Seluruh hak cipta dilindungi.</p>
                <p>Program Magang & Prakerin Industri</p>
            </div>
        </div>
    </footer>

@stack('modals')
@stack('scripts')
</body>
</html>