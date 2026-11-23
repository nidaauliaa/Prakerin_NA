@extends('layouts.public')

@section('title', 'Dashboard Peserta - PT Mekar Armada Jaya')

@section('content')
@php
    $fotoUrl = $ada['foto'] ? route('pelamar.foto', ['v' => $pelamar->updated_at?->timestamp]) : null;
    $smk     = $profil?->tipe_magang === 'smk';

    $badge = fn (string $s) => match ($s) {
        'diterima' => ['bg-emerald-100 text-emerald-800', 'Diterima'],
        'ditolak'  => ['bg-rose-100 text-rose-800', 'Ditolak'],
        default    => ['bg-amber-100 text-amber-800', 'Menunggu'],
    };

    // Bulan dalam bahasa Indonesia (APP_LOCALE di .env masih 'en')
    $tgl = fn ($d, string $f = 'j M Y') => $d->copy()->locale('id')->translatedFormat($f);

    $baris = fn (string $label, ?string $nilai) => [$label, filled($nilai) ? $nilai : '-'];
@endphp

<section class="py-10 bg-slate-100 min-h-[75vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Judul -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Dashboard Peserta</h1>
            <p class="text-sm text-slate-500 mt-1">Halo, {{ $pelamar->nama_depan }}. Pantau profil, riwayat lamaran, dan surat balasan prakerin Anda di sini.</p>
        </div>

        <!-- Tab -->
        <div class="flex gap-2 border-b border-slate-200 text-sm font-bold" role="tablist">
            <button type="button" data-tab="ringkasan" role="tab" class="tab-btn px-4 py-2.5 -mb-px border-b-2 flex items-center gap-2">
                <i class="fa-solid fa-user"></i> Ringkasan
            </button>
            <button type="button" data-tab="surat-keterangan" role="tab" class="tab-btn px-4 py-2.5 -mb-px border-b-2 flex items-center gap-2">
                <i class="fa-solid fa-file-signature"></i> Surat Keterangan
                @if ($keteranganTerbit > 0)
                    <span class="bg-emerald-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $keteranganTerbit }}</span>
                @endif
            </button>
        </div>

        <div id="tab-ringkasan" class="tab-isi">
        @if (! $lengkap)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-amber-900 leading-relaxed">
                    <p class="font-extrabold text-sm"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Profil Anda belum lengkap</p>
                    Lengkapi data diri sekali saja (termasuk NIS/NIM) agar bisa melamar dan dikenali HRD saat didaftarkan sebagai peserta.
                </div>
                <a href="{{ route('pelamar.profil.edit') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition whitespace-nowrap">Lengkapi Profil</a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- ========== PROFIL ========== -->
            <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-slate-50 border-b border-slate-200 flex items-center gap-4">
                    <div class="w-20 h-20 shrink-0 rounded-2xl overflow-hidden bg-slate-200 ring-2 ring-white shadow">
                        @if ($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                        @else
                            <span class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-brand-blue to-indigo-500 text-white text-2xl font-bold">{{ $pelamar->inisial }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Profil</p>
                        <h2 class="text-lg font-bold text-slate-900 leading-snug break-words">{{ $profil?->nama ?? $pelamar->nama }}</h2>
                        <p class="text-xs text-slate-500 break-all">{{ $pelamar->email }}</p>
                        @if ($profil)
                            <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $smk ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-brand-blue' }}">
                                {{ $smk ? 'Anak SMK' : 'Mahasiswa (D3/S1)' }}
                            </span>
                        @endif
                        <a href="{{ route('pelamar.profil.edit') }}" class="block mt-2 text-[11px] font-bold text-brand-blue hover:underline"><i class="fa-solid fa-pen-to-square mr-1"></i>{{ $profil ? 'Ubah Profil' : 'Lengkapi Profil' }}</a>
                    </div>
                </div>

                @if ($profil)
                    <dl class="p-6 space-y-3 text-xs">
                        @foreach ([
                            $baris('Jenis Kelamin', match ($profil->jenis_kelamin) { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => null }),
                            $baris('No. WhatsApp', $profil->no_wa),
                            $baris('Alamat', $profil->alamat),
                            $baris($smk ? 'Asal Sekolah' : 'Asal Kampus', $profil->asal_sekolah),
                            $baris('Jurusan', $profil->jurusan),
                            $baris('NIS / NIM', $profil->nis_nim),
                            $baris($smk ? 'Guru Pembimbing' : 'Dosen Pembimbing', $profil->pembimbing),
                            $baris($smk ? 'No. HP Pembimbing' : 'No. WA Dosen Pembimbing', $profil->no_wa_pembimbing),
                        ] as [$label, $nilai])
                            <div>
                                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</dt>
                                <dd class="text-slate-800 font-semibold break-words">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="px-6 pb-6 space-y-2 text-xs">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Berkas yang diunggah</p>
                        @foreach ([
                            ['cv', 'CV / Portofolio', 'fa-file-pdf text-rose-500'],
                            ['surat', 'Surat Pengajuan Sekolah / Kampus', 'fa-file-pdf text-rose-500'],
                            ['foto', 'Foto Pas', 'fa-image text-brand-blue'],
                        ] as [$kode, $nama, $ikon])
                            <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                <span class="text-slate-600"><i class="fa-solid {{ $ikon }} mr-2"></i>{{ $nama }}</span>
                                @if ($ada[$kode])
                                    <a href="{{ route('pelamar.berkas', $kode) }}" target="_blank" rel="noopener" class="font-bold text-brand-blue hover:underline whitespace-nowrap">Lihat</a>
                                @else
                                    <span class="text-slate-400 italic whitespace-nowrap">Tidak ada</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-xs text-slate-500 leading-relaxed">
                        <p>Profil belum diisi. Lengkapi sekali saja, lalu dipakai untuk semua lamaran dan data presensi.</p>
                        <a href="{{ route('pelamar.profil.edit') }}" class="inline-block mt-3 px-4 py-2 rounded-xl bg-slate-900 hover:bg-brand-blue text-white font-bold transition">Lengkapi Profil</a>
                    </div>
                @endif
            </div>

            <!-- ========== NOTIFIKASI + RIWAYAT ========== -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Notifikasi -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-bell text-brand-blue"></i> Notifikasi
                        @if ($diterima->isNotEmpty())
                            <span class="bg-emerald-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $diterima->count() }}</span>
                        @endif
                    </h2>

                    @forelse ($diterima as $l)
                        <div class="mb-3 last:mb-0 rounded-xl border border-emerald-200 bg-emerald-50 p-4 flex items-start gap-3">
                            <div class="w-10 h-10 shrink-0 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-champagne-glasses"></i>
                            </div>
                            <div class="text-xs text-emerald-900 leading-relaxed flex-1">
                                <p class="font-extrabold text-sm">Selamat, {{ $pelamar->nama_depan }}! Pengajuan prakerin Anda disetujui.</p>
                                <p class="mt-0.5">
                                    @if ($l->lowongan)
                                        Lamaran untuk posisi <strong>{{ $l->lowongan->judul }}</strong> telah disetujui HRD PT Mekar Armada Jaya.
                                    @else
                                        Anda telah didaftarkan langsung oleh HRD PT Mekar Armada Jaya sebagai peserta prakerin.
                                    @endif
                                    Periode magang: <strong>{{ $tgl($l->tgl_mulai, 'j M Y') }}</strong> s/d <strong>{{ $tgl($l->tgl_selesai, 'j M Y') }}</strong>.
                                </p>

                                @if ($l->suratBalasan)
                                    <a href="{{ route('pelamar.surat', $l) }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                                        <i class="fa-solid fa-file-arrow-down"></i> Unduh Surat Balasan Ijin Prakerin (PDF)
                                    </a>
                                @else
                                    <p class="mt-2 text-emerald-800/80 italic"><i class="fa-regular fa-clock mr-1"></i> Surat balasan ijin prakerin sedang disiapkan HRD. Tombol unduh akan muncul di sini.</p>
                                @endif

                                @if ($l->dataMagang?->suratKeterangan?->terbit())
                                    <button type="button" data-buka-tab="surat-keterangan" class="inline-flex items-center gap-2 mt-3 ml-0 sm:ml-2 px-4 py-2 rounded-xl bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100 font-bold transition shadow-sm">
                                        <i class="fa-solid fa-file-signature"></i> Surat Keterangan Prakerin sudah terbit
                                    </button>
                                @endif

                                <p class="mt-3 text-[11px] text-emerald-800/80">
                                    Presensi harian dilakukan lewat <a href="{{ route('login') }}" class="font-bold underline">halaman Login Presensi</a>
                                    @if ($l->nis_nim) (username: NIS/NIM <strong>{{ $l->nis_nim }}</strong>, kata sandi sama dengan akun ini)@endif.
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic"><i class="fa-regular fa-bell-slash mr-1"></i> Belum ada notifikasi. Kabar persetujuan dari HRD akan muncul di sini.</p>
                    @endforelse
                </div>

                <!-- Riwayat magang -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-brand-blue"></i> Riwayat Magang
                        </h2>
                        <span class="text-[11px] font-semibold text-slate-500">
                            Lamaran terkirim: <strong class="text-slate-800">{{ $riwayat->count() }}</strong> dari {{ \App\Models\Pelamar::MAKS_LAMARAN }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($riwayat as $l)
                            @php [$kelas, $label] = $badge($l->status); @endphp
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $l->lowongan?->judul ?? 'Penempatan langsung HRD (Internal)' }}</h3>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Kode: <strong class="text-slate-600">{{ $l->kode }}</strong> &middot; Dikirim {{ $tgl($l->created_at, 'j M Y') }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $kelas }}">{{ $label }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mt-3 text-xs">
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Periode diajukan</span>
                                        <span class="font-semibold text-slate-700">{{ $tgl($l->tgl_mulai, 'j M Y') }} s/d {{ $tgl($l->tgl_selesai, 'j M Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Keputusan HRD</span>
                                        <span class="font-semibold text-slate-700">{{ $l->diputuskan_pada ? $tgl($l->diputuskan_pada, 'j M Y') : 'Belum diputuskan' }}</span>
                                    </div>
                                </div>

                                @if ($l->status === 'ditolak' && $l->catatan_admin)
                                    <p class="mt-3 text-xs bg-rose-50 border border-rose-100 text-rose-800 rounded-lg px-3 py-2"><strong>Catatan HRD:</strong> {{ $l->catatan_admin }}</p>
                                @endif

                                @if ($l->status === 'diterima')
                                    <div class="mt-3">
                                        @if ($l->suratBalasan)
                                            <a href="{{ route('pelamar.surat', $l) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-blue text-white text-xs font-bold transition">
                                                <i class="fa-solid fa-file-arrow-down"></i> Unduh Surat Balasan (PDF)
                                            </a>
                                        @else
                                            <span class="text-[11px] text-slate-400 italic"><i class="fa-regular fa-clock mr-1"></i> Surat balasan sedang disiapkan HRD</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada lamaran. Pilih posisi di halaman lowongan untuk mulai mendaftar.</p>
                        @endforelse
                    </div>

                    @if ($diterima->isNotEmpty())
                        <p class="mt-4 text-[11px] text-slate-400">Anda sudah diterima di satu posisi. Satu pelamar hanya dapat diterima di satu posisi, jadi lamaran lain tidak dapat diajukan.</p>
                    @elseif ($sisa > 0)
                        <a href="{{ route('beranda') }}#lowongan" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl border border-brand-blue text-brand-blue hover:bg-blue-50 text-xs font-bold transition">
                            <i class="fa-solid fa-plus"></i> Lamar posisi lain (sisa {{ $sisa }})
                        </a>
                    @else
                        <p class="mt-4 text-[11px] text-slate-400">Batas maksimal {{ \App\Models\Pelamar::MAKS_LAMARAN }} lamaran per pelamar sudah tercapai.</p>
                    @endif
                </div>
            </div>
        </div>
        </div><!-- /tab-ringkasan -->

        <!-- ========== TAB SURAT KETERANGAN ========== -->
        <div id="tab-surat-keterangan" class="tab-isi hidden space-y-6">
            @php $tanggalSurat = fn ($d) => \App\Models\SuratKeterangan::tanggalIndonesia($d); @endphp

            @forelse ($keterangan as $l)
                @php
                    $sk = $l->dataMagang?->suratKeterangan;
                    $sk?->setRelation('dataMagang', $l->dataMagang);   // hindari query ulang di pratinjau
                    $siap = $sk && $sk->terbit() && $sk->lengkap();
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Surat Keterangan Prakerin</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $l->lowongan?->judul ?? '-' }} &middot; {{ $tgl($l->tgl_mulai, 'j M Y') }} s/d {{ $tgl($l->tgl_selesai, 'j M Y') }}
                            </p>
                        </div>
                        @if ($siap)
                            <a href="{{ route('pelamar.keterangan', $sk) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                                <i class="fa-solid fa-file-arrow-down"></i> Unduh PDF
                            </a>
                        @endif
                    </div>

                    @if ($siap)
                        <div class="p-5 sm:p-8 bg-slate-100">
                            <div class="sk-kertas mx-auto max-w-[640px] bg-white shadow-md border border-slate-200 px-6 sm:px-12 py-10 text-[13px] leading-relaxed text-black">
                                @include('surat.partials.keterangan-isi', ['surat' => $sk, 'cfg' => $cfgKeterangan, 'tanggal' => $tanggalSurat])
                            </div>
                        </div>
                    @elseif ($sk)
                        <div class="p-6 text-xs text-slate-500">
                            <p><i class="fa-regular fa-clock mr-1"></i> Penilaian prakerin Anda sudah masuk. Surat keterangan sedang disiapkan HRD dan akan tampil di sini setelah diterbitkan.</p>
                        </div>
                    @else
                        <div class="p-6 text-xs text-slate-500">
                            <p><i class="fa-regular fa-hourglass-half mr-1"></i> Surat keterangan akan tersedia setelah masa prakerin selesai dan penilaian dari pembimbing lapangan diisi.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs text-slate-500">
                    <p><i class="fa-regular fa-file-lines mr-1"></i> Belum ada surat keterangan. Menu ini terisi setelah lamaran Anda diterima dan prakerin selesai dinilai.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
    .tab-btn { color: #64748b; border-color: transparent; }
    .tab-btn:hover { color: #0f172a; }
    .tab-btn.aktif { color: #2563eb; border-color: #2563eb; }
    /* Pratinjau surat keterangan (isi sama dengan PDF) */
    .sk-kertas p { margin: 0 0 .65rem; text-align: justify; }
    .sk-kertas .sk-judul { text-align: center; font-size: 1.15rem; font-weight: 800; text-decoration: underline; margin: 0 0 .15rem; }
    .sk-kertas .sk-nomor { text-align: center; margin-bottom: 1.4rem; }
    .sk-kertas .sk-tengah { text-align: center; }
    .sk-kertas table.sk-peserta { width: 100%; border-collapse: collapse; margin: .3rem 0 1rem; }
    .sk-kertas table.sk-peserta th, .sk-kertas table.sk-peserta td { border: 1px solid #000; padding: .4rem .5rem; text-align: center; }
    .sk-kertas table.sk-peserta th { background: #efefef; font-size: .75rem; }
    .sk-kertas .sk-ttd { margin-top: 1.6rem; }
    .sk-kertas .sk-ttd p { text-align: left; }
    .sk-kertas .sk-ruang-ttd { height: 56px; }
</style>
@endsection

@push('scripts')
<script>
    // Tab dashboard: Ringkasan | Surat Keterangan (ingat pilihan lewat #hash)
    (function () {
        const isi = document.querySelectorAll('.tab-isi');
        const tombol = document.querySelectorAll('.tab-btn');
        function buka(nama) {
            if (!document.getElementById('tab-' + nama)) nama = 'ringkasan';
            isi.forEach(e => e.classList.toggle('hidden', e.id !== 'tab-' + nama));
            tombol.forEach(b => b.classList.toggle('aktif', b.dataset.tab === nama));
            history.replaceState(null, '', nama === 'ringkasan' ? location.pathname + location.search : '#' + nama);
        }
        tombol.forEach(b => b.addEventListener('click', () => buka(b.dataset.tab)));
        document.querySelectorAll('[data-buka-tab]').forEach(b => b.addEventListener('click', () => { buka(b.dataset.bukaTab); window.scrollTo({ top: 0, behavior: 'smooth' }); }));
        buka(location.hash.replace('#', '') || 'ringkasan');
    })();
</script>
@endpush
