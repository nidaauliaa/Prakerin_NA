<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pelamar - PT Mekar Armada Jaya</title>
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
</head>
<body class="font-sans antialiased text-slate-800 bg-white">
<div class="min-h-screen grid lg:grid-cols-2">

    {{-- ===== KIRI: tentang prakerin (50%) ===== --}}
    <aside class="relative overflow-hidden bg-gradient-to-br from-sky-50/90 via-white to-rose-50/50 border-r border-slate-100 px-6 sm:px-10 xl:px-14 py-7 lg:py-8 flex flex-col justify-between">
        <div class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 w-72 h-72 rounded-full bg-rose-200/40 blur-3xl"></div>

        <!-- Header -->
        <div class="relative flex items-center justify-between">
            <a href="{{ route('beranda') }}" class="flex flex-col leading-none">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-brand-red">new <span class="text-brand-blue">armada</span></span>
                <span class="text-[9px] font-bold text-slate-500 tracking-widest uppercase mt-1">PT Mekar Armada Jaya</span>
            </a>
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand-blue bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-2xs transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Beranda
            </a>
        </div>

        <!-- Konten Utama Kiri (justify-between agar judul naik & alur singkat turun) -->
        <div class="relative flex-1 flex flex-col justify-between pt-12 lg:pt-16 pb-4 max-w-2xl">
                    <div>
                        <span class="inline-flex items-center gap-2 text-[10px] font-bold text-brand-blue bg-blue-100/80 px-2.5 py-1 rounded-full uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-blue"></span> Portal Prakerin & Magang
                        </span>

                        <h1 class="text-2xl sm:text-3xl xl:text-[2.05rem] font-extrabold text-slate-900 leading-snug tracking-tight">
                            Mulai pengalaman kerja nyata di <span class="text-brand-blue">industri</span>, <span class="text-brand-red">bersama kami</span>.
                        </h1>

                        <p class="mt-2.5 text-xs text-slate-600 leading-relaxed max-w-xl">
                            PT Mekar Armada Jaya membuka program prakerin dan magang di Magelang bagi siswa SMK dan mahasiswa.
                            Lewat portal ini kamu bisa melamar posisi yang sesuai jurusan, mengirim berkas, dan memantau seluruh prosesnya
                            di satu tempat.
                        </p>
                    </div>

            <!-- 4 Kartu Fitur -->
            <div class="my-6 grid sm:grid-cols-2 gap-2.5">
                <div class="flex items-start gap-2.5 bg-white/90 backdrop-blur border border-blue-100/80 rounded-xl p-3 shadow-2xs">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center mt-0.5"><i class="fa-solid fa-briefcase text-xs"></i></span>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Posisi Sesuai Jurusan</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Pilih section dan periode yang cocok dengan kompetensimu.</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 bg-white/90 backdrop-blur border border-rose-100/80 rounded-xl p-3 shadow-2xs">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-50 text-brand-red flex items-center justify-center mt-0.5"><i class="fa-solid fa-file-circle-check text-xs"></i></span>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Berkas Terpusat</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Data diri, CV, dan surat pengantar dikirim sekali lewat portal.</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 bg-white/90 backdrop-blur border border-blue-100/80 rounded-xl p-3 shadow-2xs">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center mt-0.5"><i class="fa-solid fa-bell text-xs"></i></span>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Status Transparan</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Pantau hasil seleksi dan notifikasi langsung dari dashboard.</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 bg-white/90 backdrop-blur border border-rose-100/80 rounded-xl p-3 shadow-2xs">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-50 text-brand-red flex items-center justify-center mt-0.5"><i class="fa-solid fa-envelope-open-text text-xs"></i></span>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Surat Balasan Digital</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Surat balasan ijin prakerin bisa diunduh setelah lamaran diterima.</p>
                    </div>
                </div>
            </div>

            <!-- Alur Singkat (Turun ke bagian bawah area konten) -->
            <div class="bg-white/75 backdrop-blur border border-slate-200/70 rounded-xl px-4 py-3">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Alur singkat</p>
                <ol class="flex flex-wrap items-center justify-between gap-2 text-[11px] font-semibold text-slate-600">
                    <li class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-full bg-brand-blue text-white text-[9px] flex items-center justify-center">1</span> Pilih posisi</li>
                    <li class="text-slate-300"><i class="fa-solid fa-chevron-right text-[8px]"></i></li>
                    <li class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-full bg-brand-blue text-white text-[9px] flex items-center justify-center">2</span> Masuk / daftar</li>
                    <li class="text-slate-300"><i class="fa-solid fa-chevron-right text-[8px]"></i></li>
                    <li class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-full bg-brand-blue text-white text-[9px] flex items-center justify-center">3</span> Lengkapi data</li>
                    <li class="text-slate-300"><i class="fa-solid fa-chevron-right text-[8px]"></i></li>
                    <li class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-full bg-brand-red text-white text-[9px] flex items-center justify-center">4</span> Tunggu hasil</li>
                </ol>
            </div>
        </div>

        <!-- Footer -->
        <p class="relative text-[11px] text-slate-400">© {{ date('Y') }} PT Mekar Armada Jaya</p>
    </aside>

    {{-- ===== KANAN: form login (50%) ===== --}}
    <main class="bg-white flex items-center justify-center px-6 sm:px-10 py-10">
        <div class="w-full max-w-sm">
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Masuk ke Akun Anda</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Masuk untuk melanjutkan lamaran prakerin / magang.</p>
                <div class="mt-3.5 h-1 w-12 rounded-full bg-gradient-to-r from-brand-blue to-brand-red"></div>
            </div>

            @if (session('info'))
                <div class="mb-4 p-3 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200 flex items-start space-x-2.5">
                    <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5"></i>
                    <div class="leading-relaxed">{{ session('info') }}</div>
                </div>
            @endif

            @if ($draft)
                <div class="mb-4 p-3 rounded-xl text-xs bg-blue-50 text-blue-900 border border-blue-200 flex items-start space-x-2.5">
                    <i class="fa-regular fa-calendar-check text-brand-blue text-sm mt-0.5"></i>
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

            <form method="POST" action="{{ route('pelamar.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com"
                               class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/25 focus:border-brand-blue transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-blue/25 focus:border-brand-blue transition">
                    </div>
                </div>
                <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer">
                    <input type="checkbox" name="ingat" value="1" class="rounded border-slate-300 text-brand-blue">
                    <span>Ingat saya</span>
                </label>
                <button type="submit" class="w-full py-2.5 bg-brand-blue hover:bg-brand-darkBlue text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                    Masuk <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                </button>
            </form>

            <p class="text-xs text-center text-slate-500 mt-6">
                Belum punya akun?
                <a href="{{ route('pelamar.register') }}" class="font-bold text-brand-red hover:underline">Daftar sekarang</a>
            </p>
            <p class="text-[11px] text-center text-slate-400 mt-2.5">
                Peserta magang aktif? Masuk lewat
                <a href="{{ route('login') }}" class="underline hover:text-slate-600">login absensi</a>.
            </p>
        </div>
    </main>
</div>
</body>
</html>