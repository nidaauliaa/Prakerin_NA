<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $form->judul }} - PT Mekar Armada Jaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
            colors: { brand: { blue: '#3b82f6', purpleBlue: '#6366f1', red: '#e11d48' } } } } }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen">
<div class="max-w-2xl mx-auto px-4 py-8 space-y-5">

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-brand-blue to-brand-purpleBlue"></div>
        <div class="p-6">
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">PT Mekar Armada Jaya &middot; Penilaian Prakerin</p>
            <h1 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $form->judul }}</h1>
            @if ($form->deskripsi)
                <p class="text-sm text-slate-600 mt-2 whitespace-pre-line">{{ $form->deskripsi }}</p>
            @endif
        </div>
    </div>

    @if (session('terkirim'))
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-6 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl"><i class="fa-solid fa-check"></i></div>
            <h2 class="text-lg font-extrabold text-slate-900 mt-3">Penilaian terkirim</h2>
            <p class="text-sm text-slate-600 mt-1">Terima kasih. Penilaian untuk <strong>{{ session('terkirim') }}</strong> sudah tersimpan.</p>
            @if ($form->dibuka() && $peserta->isNotEmpty())
                <a href="{{ route('penilaian.isi', $form->kode) }}" class="inline-block mt-4 px-4 py-2 rounded-xl bg-slate-900 hover:bg-brand-blue text-white text-xs font-bold transition">Nilai peserta lain</a>
            @endif
        </div>
    @elseif (! $form->dibuka())
        <div class="bg-white rounded-2xl border border-rose-200 shadow-sm p-6 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl"><i class="fa-solid fa-lock"></i></div>
            <h2 class="text-lg font-extrabold text-slate-900 mt-3">Form sudah ditutup</h2>
            <p class="text-sm text-slate-600 mt-1">Hubungi HRD PT Mekar Armada Jaya bila Anda masih perlu mengisi penilaian.</p>
        </div>
    @elseif ($peserta->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-2xl"><i class="fa-solid fa-user-check"></i></div>
            <h2 class="text-lg font-extrabold text-slate-900 mt-3">Tidak ada peserta yang perlu dinilai</h2>
            <p class="text-sm text-slate-600 mt-1">Semua peserta pada form ini sudah dinilai.</p>
        </div>
    @else
        @if ($errors->any())
            <div class="p-4 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                <strong>Mohon periksa kembali:</strong>
                <ul class="list-disc ml-4 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('penilaian.kirim', $form->kode) }}" class="space-y-5" onsubmit="return confirm('Kirim penilaian? Setelah dikirim, penilaian tidak bisa diubah sendiri.')">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 text-sm">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Peserta yang dinilai <span class="text-rose-500">*</span></label>
                    <select name="data_magang_id" required class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="">- Pilih peserta -</option>
                        @foreach ($peserta as $p)
                            <option value="{{ $p->id }}" @selected((string) old('data_magang_id') === (string) $p->id)>{{ $p->nama }} &mdash; {{ $p->asal_sekolah }}{{ $p->jurusan ? ' (' . $p->jurusan . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Nama penilai <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_penilai" required maxlength="150" value="{{ old('nama_penilai') }}" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Jabatan <span class="text-rose-500">*</span></label>
                        <input type="text" name="jabatan_penilai" required maxlength="150" value="{{ old('jabatan_penilai') }}" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-sm">
                <h2 class="font-extrabold text-slate-900">Penilaian</h2>
                <p class="text-xs text-slate-500 mt-1 mb-4">Beri nilai angka 0 - 100 untuk setiap butir.</p>

                <div class="divide-y divide-slate-200">
                    @foreach ($form->kriteria as $k)
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-800">{{ $loop->iteration }}. {{ $k->nama }} <span class="text-rose-500">*</span></p>
                                @if ($k->keterangan) <p class="text-xs text-slate-500 mt-0.5">{{ $k->keterangan }}</p> @endif
                            </div>
                            <input type="number" name="nilai[{{ $k->id }}]" required min="0" max="100" step="1" inputmode="numeric" placeholder="0 - 100"
                                   value="{{ old("nilai.{$k->id}") }}" class="w-full sm:w-32 px-3 py-2.5 text-center font-bold bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                    @endforeach
                </div>

                <div class="mt-2">
                    <label class="block font-bold text-slate-800 mb-1">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <textarea name="catatan" rows="3" maxlength="1000" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Penilaian
                </button>
            </div>
        </form>
    @endif

    <p class="text-center text-[11px] text-slate-400">PT Mekar Armada Jaya &middot; Portal Prakerin</p>
</div>
</body>
</html>
