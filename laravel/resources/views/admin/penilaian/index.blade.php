@extends('layouts.admin-portal')

@section('title', 'Form Penilaian - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Form Penilaian Prakerin</h1>
            <p class="text-xs text-slate-500 mt-1">Buat form penilaian, kirim linknya ke penilai (mentor / pembimbing lapangan), lalu nilainya otomatis masuk ke Surat Keterangan peserta.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.surat-keterangan.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition flex items-center gap-2">
                <i class="fa-solid fa-file-signature"></i> Surat Keterangan
            </a>
            <a href="{{ route('admin.penilaian.create') }}" class="bg-gradient-to-r from-brand-blue to-brand-purpleBlue text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Buat Form Penilaian
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-4">Form</th>
                        <th class="p-4">Peserta</th>
                        <th class="p-4">Butir</th>
                        <th class="p-4">Terisi</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Link untuk penilai</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($forms as $f)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <a href="{{ route('admin.penilaian.show', $f) }}" class="font-bold text-slate-900 hover:text-brand-blue block">{{ $f->judul }}</a>
                                <span class="text-[11px] text-slate-400">Dibuat {{ $f->created_at->locale('id')->translatedFormat('j M Y') }}</span>
                            </td>
                            <td class="p-4 text-slate-700">{{ $f->departemen?->nama ?? 'Semua departemen' }}</td>
                            <td class="p-4 font-bold text-slate-800">{{ $f->kriteria_count }}</td>
                            <td class="p-4 font-bold text-slate-800">{{ $f->penilaian_count }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $f->dibuka() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $f->dibuka() ? 'Dibuka' : 'Ditutup' }}</span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    <input type="text" readonly value="{{ $f->link() }}" onclick="this.select()" class="w-52 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-600">
                                    <button type="button" onclick="salinLink(this, @js($f->link()))" class="px-2.5 py-1.5 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-lg transition whitespace-nowrap">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.penilaian.show', $f) }}" class="px-3 py-1.5 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-lg transition">Hasil</a>
                                    <a href="{{ route('admin.penilaian.edit', $f) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.penilaian.status', $f) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg font-bold transition {{ $f->dibuka() ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">{{ $f->dibuka() ? 'Tutup' : 'Buka' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.penilaian.destroy', $f) }}" onsubmit="return confirm('Hapus form {{ addslashes($f->judul) }} beserta {{ $f->penilaian_count }} isian di dalamnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg font-bold text-rose-600 hover:bg-rose-50 transition" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">Belum ada form penilaian. Klik "Buat Form Penilaian".</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function salinLink(btn, teks) {
        const ok = () => { const h = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-check"></i> Tersalin'; setTimeout(() => btn.innerHTML = h, 1500); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(teks).then(ok);
        } else {
            const t = document.createElement('textarea'); t.value = teks; document.body.appendChild(t); t.select();
            document.execCommand('copy'); t.remove(); ok();
        }
    }
</script>
@endpush
