@extends('layouts.admin-portal')

@section('title', 'Hasil Penilaian - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.penilaian.index') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-blue"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $form->judul }}</h1>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $form->departemen?->nama ?? 'Semua departemen' }} &middot;
                    <span class="font-bold {{ $form->dibuka() ? 'text-emerald-600' : 'text-rose-600' }}">{{ $form->dibuka() ? 'Dibuka' : 'Ditutup' }}</span> &middot;
                    {{ $hasil->count() }} terisi, {{ $sisa }} peserta belum dinilai
                </p>
            </div>
            <a href="{{ route('admin.surat-keterangan.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition flex items-center gap-2 self-start">
                <i class="fa-solid fa-file-signature"></i> Lanjut ke Surat Keterangan
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 text-xs flex flex-wrap items-center gap-2">
        <span class="font-bold text-slate-600">Link form:</span>
        <input type="text" readonly value="{{ $form->link() }}" onclick="this.select()" class="flex-1 min-w-[16rem] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-600">
        <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(@js($form->link())); this.textContent='Tersalin'" class="px-3 py-2 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-lg transition">Salin</button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-4">Peserta</th>
                        <th class="p-4">Penilai</th>
                        <th class="p-4">Nilai Akhir</th>
                        <th class="p-4">Diisi</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($hasil as $h)
                        <tr class="hover:bg-slate-50 transition align-top">
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block">{{ $h->dataMagang?->nama ?? '(peserta dihapus)' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $h->dataMagang?->asal_sekolah }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-800 block">{{ $h->nama_penilai }}</span>
                                <span class="text-[11px] text-slate-400">{{ $h->jabatan_penilai }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-base font-extrabold text-slate-900">{{ number_format($h->nilai_akhir, 2, ',', '.') }}</span>
                                <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-brand-blue">{{ $h->predikat }}</span>
                            </td>
                            <td class="p-4 text-slate-500">{{ $h->created_at->locale('id')->translatedFormat('j M Y, H:i') }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="document.getElementById('rinci-{{ $h->id }}').classList.toggle('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">Rincian</button>
                                    <form method="POST" action="{{ route('admin.penilaian.hasil.destroy', $h) }}" onsubmit="return confirm('Hapus penilaian untuk {{ addslashes($h->dataMagang?->nama ?? 'peserta ini') }}? Nilai di surat keterangannya ikut dihitung ulang.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg font-bold text-rose-600 hover:bg-rose-50 transition" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="rinci-{{ $h->id }}" class="hidden bg-slate-50/70">
                            <td colspan="5" class="px-4 pb-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 max-w-2xl">
                                    @foreach ($h->nilai as $n)
                                        <div class="flex justify-between border-b border-slate-200 py-1">
                                            <span>{{ $n->nama_kriteria }} <span class="text-slate-400">(bobot {{ $n->bobot }})</span></span>
                                            <strong class="text-slate-900">{{ $n->nilai }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($h->catatan)
                                    <p class="mt-3 text-slate-600"><strong>Catatan penilai:</strong> {{ $h->catatan }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada yang mengisi. Bagikan link form di atas ke penilai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
