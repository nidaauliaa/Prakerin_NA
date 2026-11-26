<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - PT Mekar Armada Jaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: { brand: { blue: '#2563eb', darkBlue: '#1d4ed8', red: '#e11d48', navy: '#0f172a' } }
            } }
        }
    </script>
    <style>
        /* Ruang scrollbar selalu dicadangkan: lebar halaman tidak berubah antara login & daftar. */
        html { scrollbar-gutter: stable; }
        /* Tailwind CDN baru menyusun style setelah halaman dimuat; body ditahan sebentar lalu fade-in
           supaya perpindahan login <-> daftar tidak berkedip / melompat. */
        body { transition: opacity .18s ease; }
        html:not(.siap) body { opacity: 0; }
    </style>
    <noscript><style>html:not(.siap) body { opacity: 1; }</style></noscript>
</head>
<body class="font-sans antialiased text-slate-800 min-h-screen relative overflow-x-hidden bg-gradient-to-br from-sky-100/75 via-slate-50 to-rose-100/65">

    {{-- Dekorasi blur lembut menyatu --}}
    <div class="pointer-events-none fixed -top-28 -left-24 w-96 h-96 rounded-full bg-blue-300/25 blur-3xl"></div>
    <div class="pointer-events-none fixed top-1/3 left-1/3 w-80 h-80 rounded-full bg-sky-200/25 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-28 -right-24 w-96 h-96 rounded-full bg-rose-300/25 blur-3xl"></div>

    <div class="relative min-h-screen flex flex-col justify-between max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 lg:py-7">

        {{-- Topbar --}}
        <header class="flex items-center justify-between gap-4">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-brand-blue text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                </span>
                <div class="leading-tight">
                    <span class="block text-base font-black tracking-tight text-brand-red">new <span class="text-brand-blue">armada</span></span>
                    <span class="block text-[10px] font-bold text-slate-500 tracking-wider uppercase">PT Mekar Armada Jaya</span>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 bg-white/80 backdrop-blur px-3 py-1.5 rounded-full border border-white shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Portal Prakerin & Magang
                </span>
                <a href="{{ route('beranda') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brand-blue bg-white/90 px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-sm transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Beranda
                </a>
            </div>
        </header>

        {{-- Konten utama: rasio 45:55 --}}
        <main class="my-auto py-6 lg:py-4 lg:min-h-[40rem] grid lg:grid-cols-[9fr_11fr] gap-8 lg:gap-12 items-stretch">

            {{-- Kiri (45%): tipografi bersih + list keunggulan tanpa kotak bertumpuk --}}
            <section class="flex flex-col justify-between py-2 lg:py-4">
                <div class="space-y-6">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-brand-blue bg-white/85 px-3.5 py-1 rounded-full border border-blue-100/80 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-blue"></span> Portal Prakerin & Magang
                    </span>

                    <div class="space-y-3">
                        <h1 class="text-3xl xl:text-[2.35rem] font-extrabold text-slate-900 leading-[1.18] tracking-tight">
                            Mulai pengalaman kerja nyata di <span class="text-brand-blue">industri</span>, <span class="text-brand-red">bersama kami.</span>
                        </h1>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-xl">
                            PT Mekar Armada Jaya membuka program prakerin dan magang di Magelang bagi siswa SMK dan mahasiswa.
                            Lewat portal ini kamu bisa melamar posisi yang sesuai jurusan, mengirim berkas, dan memantau seluruh prosesnya
                            di satu tempat.
                        </p>
                    </div>

                    {{-- 4 Keunggulan: layout list bersih agar tidak menumpuk kotak --}}
                    <div class="grid sm:grid-cols-2 gap-x-6 gap-y-5 pt-2 border-t border-slate-200/70">
                        <div class="flex items-start gap-3 pt-2">
                            <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-600/10 text-brand-blue flex items-center justify-center mt-0.5">
                                <i class="fa-solid fa-briefcase text-xs"></i>
                            </span>
                            <div class="space-y-1">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">Posisi Sesuai Jurusan</h2>
                                <p class="text-xs text-slate-500 leading-relaxed">Pilih section dan periode yang cocok dengan kompetensimu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pt-2">
                            <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-600/10 text-brand-red flex items-center justify-center mt-0.5">
                                <i class="fa-solid fa-file-circle-check text-xs"></i>
                            </span>
                            <div class="space-y-1">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">Berkas Terpusat</h2>
                                <p class="text-xs text-slate-500 leading-relaxed">Data diri, CV, dan surat pengantar dikirim sekali lewat portal.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pt-2">
                            <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-600/10 text-brand-blue flex items-center justify-center mt-0.5">
                                <i class="fa-solid fa-bell text-xs"></i>
                            </span>
                            <div class="space-y-1">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">Status Transparan</h2>
                                <p class="text-xs text-slate-500 leading-relaxed">Pantau hasil seleksi dan notifikasi langsung dari dashboard.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pt-2">
                            <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-600/10 text-brand-red flex items-center justify-center mt-0.5">
                                <i class="fa-solid fa-envelope-open-text text-xs"></i>
                            </span>
                            <div class="space-y-1">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">Surat Balasan Digital</h2>
                                <p class="text-xs text-slate-500 leading-relaxed">Surat balasan ijin prakerin bisa diunduh setelah lamaran diterima.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alur singkat --}}
                <div class="mt-8 pt-4 border-t border-slate-200/70">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Alur singkat</p>
                    <ol class="grid grid-cols-2 xl:grid-cols-4 gap-2.5">
                        @foreach ([['Pilih posisi', 'brand-blue'], ['Masuk / daftar', 'brand-blue'], ['Lengkapi data', 'brand-blue'], ['Tunggu hasil', 'brand-red']] as $i => [$label, $warna])
                            <li class="flex items-center gap-2 bg-white/75 backdrop-blur border border-white rounded-xl px-3 py-2 shadow-sm">
                                <span class="shrink-0 w-5 h-5 rounded-full bg-{{ $warna }} text-white text-[10px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                <span class="text-xs font-semibold text-slate-700 leading-tight">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Kanan (55%): form login / daftar --}}
            <section class="flex items-center justify-center">
                <div class="w-full max-w-md bg-white/95 backdrop-blur-md border border-white rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-900/5">
                    <div class="text-center mb-5">
                        <div class="w-11 h-11 rounded-2xl bg-brand-blue text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/20">
                            <i class="fa-solid @yield('ikon') text-sm"></i>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-900">@yield('judul')</h2>
                        <p class="text-xs text-slate-500 mt-1">@yield('subjudul')</p>
                    </div>

                    @if (session('info'))
                        <div class="mb-4 p-3 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200 flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                            <div class="leading-relaxed">{{ session('info') }}</div>
                        </div>
                    @endif

                    @if ($draft)
                        <div class="mb-4 p-3 rounded-xl text-xs bg-blue-50 text-blue-900 border border-blue-200 flex items-start gap-2.5">
                            <i class="fa-regular fa-calendar-check text-brand-blue text-sm mt-0.5 shrink-0"></i>
                            <div class="leading-relaxed">
                                Lamaran Anda: <strong>{{ $draft['posisi'] }}</strong><br>
                                Periode: <strong>{{ $draft['mulai'] }} s/d {{ $draft['selesai'] }}</strong>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                            <strong>Mohon periksa kembali:</strong>
                            <ul class="list-disc ml-4 mt-1 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('form')

                    <div class="mt-5 pt-4 border-t border-slate-100 text-center space-y-2">
                        @yield('tautan')
                        <p class="text-[11px] text-slate-400">
                            Absensi Peserta magang aktif? Masuk lewat
                            <a href="{{ route('login') }}" class="underline hover:text-slate-600">login absensi</a>.
                        </p>
                    </div>
                </div>
            </section>

        </main>

        {{-- Footer --}}
        <footer class="flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400 pt-2">
            <span>© {{ date('Y') }} PT Mekar Armada Jaya</span>
            <span>Portal Prakerin & Magang</span>
        </footer>

    </div>
<script>
    (function () {
        var tampil = function () { document.documentElement.classList.add('siap'); };
        window.addEventListener('load', function () { requestAnimationFrame(tampil); });
        setTimeout(tampil, 1200); // cadangan kalau CDN lambat
        // Fokus ke field pertama tanpa menggeser/menggulung halaman (pengganti atribut autofocus).
        var f = document.querySelector('form input:not([type=hidden])');
        if (f) f.focus({ preventScroll: true });
    })();
</script>
</body>
</html>