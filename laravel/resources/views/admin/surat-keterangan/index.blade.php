@extends('layouts.admin-portal')

@section('title', 'Surat Keterangan - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Surat Keterangan Selesai Prakerin</h1>
            <p class="text-xs text-slate-500 mt-1">Surat muncul otomatis (draft) begitu peserta dinilai. Lengkapi nomor &amp; tanggal, lalu terbitkan agar tampil di Dashboard Peserta.</p>
        </div>
        <a href="{{ route('admin.penilaian.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-clipboard-check"></i> Form Penilaian
        </a>
    </div>

    <div class="flex gap-2 text-xs font-semibold">
        @foreach (['semua' => 'Semua', 'draft' => 'Draft (' . $hitung['draft'] . ')', 'terbit' => 'Terbit (' . $hitung['terbit'] . ')'] as $k => $label)
            <a href="{{ route('admin.surat-keterangan.index', ['status' => $k]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $status === $k ? 'bg-brand-blue text-white font-bold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-4">Peserta</th>
                        <th class="p-4">Nilai</th>
                        <th class="p-4">No. Surat</th>
                        <th class="p-4">Penempatan</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($surat as $s)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block">{{ $s->dataMagang->nama }}</span>
                                <span class="text-[11px] text-slate-400">{{ $s->dataMagang->asal_sekolah }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-base font-extrabold text-slate-900">{{ number_format((float) $s->nilai_akhir, 2, ',', '.') }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $s->predikat }} &middot; {{ $s->jumlah_penilaian }} penilaian</span>
                            </td>
                            <td class="p-4">{{ $s->nomor_surat ? $s->nomorLengkap() : '-' }}</td>
                            <td class="p-4">{{ $s->penempatan ?: '-' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $s->terbit() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $s->terbit() ? 'Terbit' : 'Draft' }}</span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <button type="button" onclick="bukaForm({{ $s->id }})" class="px-3 py-1.5 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-lg transition">Edit</button>
                                    @if ($s->lengkap())
                                        <a href="{{ route('admin.surat-keterangan.pdf', $s) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">PDF</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.surat-keterangan.sinkron', $s) }}" onsubmit="return confirm('Ambil ulang nilai dari data penilaian terbaru?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition" title="Perbarui nilai dari penilaian"><i class="fa-solid fa-rotate"></i></button>
                                    </form>
                                    @if ($s->terbit())
                                        <form method="POST" action="{{ route('admin.surat-keterangan.tarik', $s) }}" onsubmit="return confirm('Tarik surat ini? Peserta tidak bisa melihatnya lagi sampai diterbitkan ulang.')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg font-bold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">Tarik</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.surat-keterangan.terbitkan', $s) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">Terbitkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada surat. Surat dibuat otomatis setelah ada penilaian yang masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $surat->links() }}
</div>
@endsection

@push('modals')
<div id="formModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 bg-slate-900 text-white flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold">Data Surat Keterangan</h3>
                <p id="fNama" class="text-xs text-slate-300"></p>
            </div>
            <button type="button" onclick="tutupForm()" class="text-slate-400 hover:text-white p-1"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="suratForm" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-6 space-y-4 overflow-y-auto text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Surat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nomor_surat" id="fNomor" required maxlength="30" placeholder="045" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[11px] text-slate-400 mt-1">Bagian depan saja. Sisanya (/HCD/MAJ/PRAKERIN/bulan/tahun) otomatis.</p>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_surat" id="fTanggal" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Penempatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="penempatan" id="fPenempatan" required maxlength="150" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_mulai" id="fMulai" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_selesai" id="fSelesai" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
                <button type="button" onclick="tutupForm()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue flex items-center gap-1 shadow-sm"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const daftarSurat = @json($rows);
    const el = (id) => document.getElementById(id);

    function bukaForm(id) {
        const d = daftarSurat.find(x => x.id === id);
        if (!d) return;
        el('suratForm').action = d.url;
        el('fNama').textContent = d.nama;
        el('fNomor').value = d.nomor_surat;
        el('fTanggal').value = d.tanggal_surat;
        el('fPenempatan').value = d.penempatan;
        el('fMulai').value = d.tgl_mulai;
        el('fSelesai').value = d.tgl_selesai;
        el('formModal').classList.remove('hidden');
    }
    function tutupForm() { el('formModal').classList.add('hidden'); }
</script>
@endpush
