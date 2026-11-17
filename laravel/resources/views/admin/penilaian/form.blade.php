@extends('layouts.admin-portal')

@php $edit = $form->exists; @endphp
@section('title', ($edit ? 'Edit' : 'Buat') . ' Form Penilaian - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <a href="{{ route('admin.penilaian.index') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-blue"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $edit ? 'Edit Form Penilaian' : 'Buat Form Penilaian' }}</h1>
        <p class="text-xs text-slate-500 mt-1">Setiap butir dinilai 0 - 100 oleh penilai. Nilai akhir = rata-rata berbobot semua butir.</p>
    </div>

    <form method="POST" action="{{ $edit ? route('admin.penilaian.update', $form) : route('admin.penilaian.store') }}" class="space-y-6">
        @csrf
        @if ($edit) @method('PUT') @endif

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Judul Form <span class="text-rose-500">*</span></label>
                <input type="text" name="judul" required maxlength="150" value="{{ old('judul', $form->judul) }}" placeholder="Contoh: Penilaian Akhir Prakerin Periode November 2026"
                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Petunjuk untuk penilai <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea name="deskripsi" rows="2" maxlength="1000" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('deskripsi', $form->deskripsi) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Daftar peserta yang tampil</label>
                    <select name="departemen_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="">Semua departemen</option>
                        @foreach ($departemen as $d)
                            <option value="{{ $d->id }}" @selected((string) old('departemen_id', $form->departemen_id) === (string) $d->id)>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Pilih departemen agar penilai hanya melihat peserta di departemennya.</p>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="dibuka" @selected(old('status', $form->status) === 'dibuka')>Dibuka (link bisa diisi)</option>
                        <option value="ditutup" @selected(old('status', $form->status) === 'ditutup')>Ditutup</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 text-xs">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-bold text-slate-900">Butir Penilaian</h2>
                <button type="button" onclick="tambahButir()" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-brand-blue text-white font-bold transition"><i class="fa-solid fa-plus mr-1"></i> Tambah Butir</button>
            </div>
            <p class="text-[11px] text-slate-400 mb-4">Bobot menentukan seberapa besar butir itu memengaruhi nilai akhir. Bobot sama semua = rata-rata biasa.</p>

            <div id="daftarButir" class="space-y-3"></div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.penilaian.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue shadow-sm flex items-center gap-1">
                <i class="fa-solid fa-floppy-disk"></i> {{ $edit ? 'Simpan Perubahan' : 'Buat Form' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const awal = @json(array_values($kriteria));
    const wadah = document.getElementById('daftarButir');
    let nomor = 0;

    function tambahButir(d = {}) {
        const i = nomor++;
        const baris = document.createElement('div');
        baris.className = 'butir border border-slate-200 rounded-xl p-3 bg-slate-50/60';
        baris.innerHTML = `
            <input type="hidden" name="kriteria[${i}][id]" value="">
            <div class="grid grid-cols-12 gap-3 items-start">
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-semibold text-slate-600 mb-1">Nama butir <span class="text-rose-500">*</span></label>
                    <input type="text" name="kriteria[${i}][nama]" required maxlength="150" class="f-nama w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                </div>
                <div class="col-span-8 sm:col-span-4">
                    <label class="block font-semibold text-slate-600 mb-1">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" name="kriteria[${i}][keterangan]" maxlength="255" class="f-ket w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                </div>
                <div class="col-span-3 sm:col-span-1">
                    <label class="block font-semibold text-slate-600 mb-1">Bobot</label>
                    <input type="number" name="kriteria[${i}][bobot]" min="1" max="100" required class="f-bobot w-full px-2 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                </div>
                <div class="col-span-1 pt-6 text-right">
                    <button type="button" onclick="hapusButir(this)" class="text-rose-500 hover:text-rose-700" title="Hapus butir"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`;
        baris.querySelector('[name$="[id]"]').value = d.id ?? '';
        baris.querySelector('.f-nama').value = d.nama ?? '';
        baris.querySelector('.f-ket').value = d.keterangan ?? '';
        baris.querySelector('.f-bobot').value = d.bobot ?? 1;
        wadah.appendChild(baris);
    }

    function hapusButir(tombol) {
        if (wadah.querySelectorAll('.butir').length <= 1) { alert('Minimal harus ada satu butir penilaian.'); return; }
        tombol.closest('.butir').remove();
    }

    (awal.length ? awal : [{}]).forEach(tambahButir);
</script>
@endpush
