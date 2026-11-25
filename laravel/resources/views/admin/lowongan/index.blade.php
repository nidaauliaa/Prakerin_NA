@extends('layouts.admin-portal')

@section('title', 'Kelola Lowongan - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Posisi &amp; Kuota Lowongan</h1>
            <p class="text-xs text-slate-500 mt-1">Tambah, perbarui, atau nonaktifkan kuota posisi magang PT Mekar Armada Jaya.</p>
        </div>
        <button type="button" onclick="bukaForm(null)" class="bg-gradient-to-r from-brand-blue to-brand-purpleBlue text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Posisi Lowongan</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-4">Posisi</th>
                        <th class="p-4">Jenjang</th>
                        <th class="p-4">Departemen</th>
                        <th class="p-4">Kuota</th>
                        <th class="p-4">Pelamar</th>
                        <th class="p-4">Batas Daftar</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($lowongan as $l)
                        @php
                            $smk = $l->tipe_magang === 'smk';
                            $badge = match ($l->status) {
                                'dibuka'  => ['bg-emerald-100 text-emerald-800', 'Dibuka'],
                                'ditutup' => ['bg-rose-100 text-rose-800', 'Ditutup'],
                                default   => ['bg-slate-200 text-slate-700', 'Draft'],
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block">{{ $l->judul }}</span>
                            </td>
                            <td class="p-4">
                                @if ($smk)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800">SMK</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-brand-blue">D3 / S1</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-700">{{ $l->departemen_nama ?? '-' }}</td>
                            <td class="p-4 font-bold text-slate-800">{{ $l->kuota }} orang</td>
                            <td class="p-4">
                                <span class="text-amber-600 font-semibold">{{ $l->menunggu_count }} menunggu</span>
                                <span class="text-slate-300 mx-1">|</span>
                                <span class="text-emerald-600 font-semibold">{{ $l->diterima_count }} diterima</span>
                            </td>
                            <td class="p-4 text-slate-500">{{ $l->batas_pendaftaran?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="bukaForm({{ $l->id }})" class="px-3 py-1.5 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-lg transition">Edit</button>

                                    <form method="POST" action="{{ route('admin.lowongan.status', $l) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg font-bold transition {{ $l->status === 'dibuka' ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
                                            {{ $l->status === 'dibuka' ? 'Tutup' : 'Buka' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.lowongan.destroy', $l) }}" onsubmit="return confirm('Hapus posisi {{ addslashes($l->judul) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg font-bold text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">Belum ada posisi lowongan. Klik "Tambah Posisi Lowongan".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- MODAL TAMBAH / EDIT POSISI -->
<div id="formModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 bg-slate-900 text-white flex items-start justify-between">
            <div>
                <h3 id="formJudul" class="text-lg font-bold">Tambah Posisi Lowongan</h3>
                <p class="text-xs text-slate-300">Posisi berstatus Dibuka akan tampil di halaman publik.</p>
            </div>
            <button type="button" onclick="tutupForm()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form id="lowonganForm" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <input type="hidden" name="_method" id="fMethod" value="POST">
            <input type="hidden" name="_id" id="fId" value="">

            <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar text-xs">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Posisi (Section) <span class="text-rose-500">*</span></label>
                    <select name="divisi_id" id="fDivisi" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="">- Pilih posisi -</option>
                        @foreach ($divisi->groupBy(fn ($d) => $d->departemen?->nama ?? 'Tanpa departemen') as $namaDept => $daftar)
                            <optgroup label="{{ $namaDept }}">
                                @foreach ($daftar as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Pilihan diambil dari daftar section. Departemen dan kualifikasi jurusan terisi otomatis.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenjang <span class="text-rose-500">*</span></label>
                        <select name="tipe_magang" id="fTipe" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="smk">SMK</option>
                            <option value="kuliah">Mahasiswa (D3/S1)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kuota Maks. <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota" id="fKuota" required min="1" max="100" value="1" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" id="fStatus" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="dibuka">Dibuka</option>
                            <option value="draft">Draft</option>
                            <option value="ditutup">Ditutup</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen <span class="text-slate-400 font-normal">(otomatis dari section)</span></label>
                        <input type="text" id="fDept" readonly tabindex="-1" placeholder="-" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Pendaftaran <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="date" name="batas_pendaftaran" id="fBatas" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kualifikasi Jurusan</label>
                    <textarea name="jurusan_diterima" id="fJurusan" rows="2" maxlength="2000" placeholder="Terisi otomatis setelah posisi dipilih, bisa diubah di sini" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Bisa diedit langsung. Perubahan hanya berlaku untuk lowongan ini, daftar section tidak ikut berubah.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Pekerjaan</label>
                    <textarea name="deskripsi" id="fDeskripsi" rows="3" maxlength="2000" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
                <button type="button" onclick="tutupForm()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue transition flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const posisi = @json($rows);
    const divisiData = @json($divisi->mapWithKeys(fn ($d) => [$d->id => ['dept' => $d->departemen?->nama, 'jurusan' => $d->jurusan]]));
    const urlStore  = @json(route('admin.lowongan.store'));
    const urlUpdate = @json(route('admin.lowongan.update', ['lowongan' => '__ID__']));
    const el = (id) => document.getElementById(id);

    function isiForm(d) {
        el('fDivisi').value = d.divisi_id ?? '';
        tampilDept();
        el('fTipe').value = d.tipe_magang ?? 'smk';
        el('fKuota').value = d.kuota ?? 1;
        el('fStatus').value = d.status ?? 'dibuka';
        el('fBatas').value = d.batas_pendaftaran ?? '';
        el('fJurusan').value = d.jurusan_diterima ?? '';
        el('fDeskripsi').value = d.deskripsi ?? '';
    }

    function tampilDept() {
        el('fDept').value = divisiData[el('fDivisi').value]?.dept ?? '';
    }

    // Pilih section -> departemen & kualifikasi jurusan terisi otomatis (jurusan tetap bisa diedit).
    el('fDivisi').addEventListener('change', () => {
        tampilDept();
        el('fJurusan').value = divisiData[el('fDivisi').value]?.jurusan ?? '';
    });

    function bukaForm(id) {
        const edit = id !== null;
        const form = el('lowonganForm');
        form.action = edit ? urlUpdate.replace('__ID__', id) : urlStore;
        el('fMethod').value = edit ? 'PUT' : 'POST';
        el('fId').value = edit ? id : '';
        el('formJudul').textContent = edit ? 'Edit Posisi Lowongan' : 'Tambah Posisi Lowongan';
        isiForm(edit ? (posisi.find(p => p.id === id) || {}) : {});
        el('formModal').classList.remove('hidden');
    }

    function tutupForm() {
        el('formModal').classList.add('hidden');
    }

    @if ($errors->any() && old('divisi_id') !== null)
        // Validasi gagal: buka lagi form dengan isian terakhir
        document.addEventListener('DOMContentLoaded', () => {
            const lama = @json(old());
            bukaForm(lama._id ? parseInt(lama._id) : null);
            isiForm(lama);
        });
    @endif
</script>
@endpush