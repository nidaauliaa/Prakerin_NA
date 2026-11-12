@extends('layouts.admin-portal')

@section('title', 'Lamaran Masuk - Admin Portal PT Mekar Armada Jaya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Lamaran Masuk</h1>
            <p class="text-xs text-slate-500 mt-1">Verifikasi berkas persyaratan SMK vs Diploma/Sarjana dan tindak lanjuti penerimaan.</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2">
            <select name="tipe" onchange="this.form.submit()" class="bg-white border border-slate-300 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-blue">
                <option value="semua" @selected($tipe === 'semua')>Semua Jenjang (SMK &amp; D3/S1)</option>
                <option value="smk" @selected($tipe === 'smk')>Khusus Anak SMK</option>
                <option value="kuliah" @selected($tipe === 'kuliah')>Khusus D3 / S1</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="bg-white border border-slate-300 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-blue">
                <option value="semua" @selected($status === 'semua')>Semua Status</option>
                <option value="menunggu" @selected($status === 'menunggu')>Menunggu Approval</option>
                <option value="diterima" @selected($status === 'diterima')>Diterima</option>
                <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-4">Nama Pelamar</th>
                        <th class="p-4">Jenjang &amp; Instansi</th>
                        <th class="p-4">Posisi Dilamar</th>
                        <th class="p-4">Tgl Daftar</th>
                        <th class="p-4">Kelengkapan Berkas</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi HRD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($lamaran as $l)
                        @php
                            $smk = $l->tipe_magang === 'smk';
                            $kurang = collect([
                                'CV' => $l->cv_path,
                                'Surat' => $l->surat_permohonan_path,
                            ])->filter(fn ($v) => ! $v)->keys();
                            $badgeStatus = match ($l->status) {
                                'diterima' => ['bg-emerald-100 text-emerald-800', 'Diterima'],
                                'ditolak'  => ['bg-rose-100 text-rose-800', 'Ditolak'],
                                default    => ['bg-amber-100 text-amber-800', 'Menunggu'],
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-900">{{ $l->nama }}</td>
                            <td class="p-4">
                                @if ($smk)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800">SMK</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-brand-blue">D3 / S1</span>
                                @endif
                                <span class="ml-1 text-slate-700 font-medium">{{ $l->asal_sekolah }}</span>
                            </td>
                            <td class="p-4 font-semibold text-slate-800">{{ $l->lowongan?->judul ?? '-' }}</td>
                            <td class="p-4 text-slate-500">{{ $l->created_at->format('Y-m-d') }}</td>
                            <td class="p-4 font-semibold {{ $kurang->isEmpty() ? 'text-emerald-600' : 'text-amber-600' }}">
                                <i class="fa-solid fa-paperclip mr-1"></i>
                                {{ $kurang->isEmpty() ? 'Lengkap' : 'Tanpa ' . $kurang->implode(' & ') }}
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badgeStatus[0] }}">{{ $badgeStatus[1] }}</span>
                                @if ($l->status === 'diterima' && ! $l->suratBalasan)
                                    <span class="block mt-1 text-[10px] font-semibold text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> Surat belum dibuat</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <button type="button" onclick="openDetailModal({{ $l->id }})" class="px-3.5 py-1.5 bg-slate-900 hover:bg-brand-blue text-white font-bold rounded-xl transition">
                                    {{ $l->status === 'menunggu' ? 'Verifikasi Berkas' : 'Lihat Detail' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada lamaran untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($lamaran->hasPages())
        <div>{{ $lamaran->links() }}</div>
    @endif
</div>
@endsection

@push('modals')
<!-- MODAL DETAIL LAMARAN -->
<div id="applicantDetailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 bg-slate-900 text-white flex items-start justify-between">
            <div>
                <span id="modalApplicantBadge" class="inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase mb-1 bg-amber-400 text-slate-900"></span>
                <h3 id="modalApplicantName" class="text-lg font-bold"></h3>
                <p id="modalApplicantPosition" class="text-xs text-slate-300"></p>
            </div>
            <button type="button" onclick="closeApplicantModal()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar text-xs">
            <div>
                <h4 class="font-bold text-slate-900 border-b border-slate-200 pb-1 mb-3 uppercase tracking-wider text-[11px]">1. Data Umum Pelamar</h4>
                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div><span class="text-slate-400 block">Email:</span><strong id="mEmail" class="text-slate-800 break-all"></strong></div>
                    <div><span class="text-slate-400 block">No. WhatsApp Peserta:</span><strong id="mWa" class="text-slate-800"></strong></div>
                    <div><span id="mAsalLabel" class="text-slate-400 block"></span><strong id="mAsal" class="text-slate-800"></strong></div>
                    <div><span class="text-slate-400 block">Jurusan:</span><strong id="mJurusan" class="text-slate-800"></strong></div>
                    <div class="col-span-2"><span class="text-slate-400 block">Alamat Domisili:</span><strong id="mAlamat" class="text-slate-800"></strong></div>
                    <div class="col-span-2"><span class="text-slate-400 block">Periode Magang Diajukan:</span><strong id="mPeriode" class="text-slate-800"></strong></div>
                    <div class="col-span-2"><span id="mPembimbingLabel" class="text-slate-400 block"></span><strong id="mPembimbing" class="text-slate-800"></strong></div>
                    <div class="col-span-2"><span class="text-slate-400 block">Tanggal Daftar:</span><strong id="mTglDaftar" class="text-slate-800"></strong></div>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 border-b border-slate-200 pb-1 mb-3 uppercase tracking-wider text-[11px]">2. Berkas Persyaratan</h4>
                <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600"><i class="fa-solid fa-image text-brand-blue mr-2"></i> Foto Pas</span>
                        <span id="berkasFoto"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200">
                        <span class="text-slate-600"><i class="fa-solid fa-file-pdf text-rose-500 mr-2"></i> CV &amp; Portfolio Peserta</span>
                        <span id="berkasCv"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200">
                        <span class="text-slate-600"><i class="fa-solid fa-file-pdf text-rose-500 mr-2"></i> Surat Pengantar Sekolah / Kampus</span>
                        <span id="berkasSurat"></span>
                    </div>
                </div>
            </div>

            <!-- Keputusan (kalau sudah diputuskan) -->
            <div id="blokKeputusan" class="hidden">
                <h4 class="font-bold text-slate-900 border-b border-slate-200 pb-1 mb-3 uppercase tracking-wider text-[11px]">3. Keputusan HRD</h4>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1">
                    <p><span class="text-slate-400">Status:</span> <strong id="mStatus"></strong></p>
                    <p><span class="text-slate-400">Diputuskan:</span> <strong id="mDiputuskan"></strong></p>
                    <p id="mCatatanWrap" class="hidden"><span class="text-slate-400">Catatan:</span> <strong id="mCatatan"></strong></p>
                    <p id="mSuratWrap" class="hidden"><span class="text-slate-400">Surat Balasan:</span> <strong id="mSurat"></strong></p>
                </div>
            </div>

            <!-- Panel Terima -->
            <form id="panelTerima" method="POST" class="hidden border border-emerald-200 bg-emerald-50 rounded-xl p-4 space-y-3">
                @csrf
                <p class="font-bold text-emerald-900 text-xs">Terima sebagai peserta magang</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">NIS / NIM <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nim" required maxlength="50" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Departemen <span class="text-rose-500">*</span></label>
                        <select name="departemen_id" id="selDepartemen" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="">- Pilih departemen -</option>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-[11px] text-emerald-800 leading-relaxed">Akun absensi dibuat otomatis (sama seperti Tambah Anak Magang): login dengan NIS/NIM dan sandi awal 12345678, wajib diganti saat login pertama.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="tampilPanel(null)" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white transition">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">Ya, Terima</button>
                </div>
            </form>

            <!-- Panel Tolak -->
            <form id="panelTolak" method="POST" class="hidden border border-rose-200 bg-rose-50 rounded-xl p-4 space-y-3">
                @csrf
                <p class="font-bold text-rose-900 text-xs">Tolak lamaran ini</p>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Catatan / alasan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <textarea name="catatan_admin" rows="2" maxlength="500" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="tampilPanel(null)" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white transition">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition">Ya, Tolak</button>
                </div>
            </form>

            <!-- Panel Surat Balasan Ijin Prakerin (muncul setelah lamaran diterima) -->
            <form id="panelSurat" method="POST" class="hidden border border-sky-200 bg-sky-50 rounded-xl p-4 space-y-3">
                @csrf
                <input type="hidden" name="_surat_lamaran" id="suratLamaranId">
                <p class="font-bold text-sky-900 text-xs"><i class="fa-solid fa-file-signature mr-1"></i> Surat Balasan Ijin Prakerin</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nomor Surat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nomor_surat" required maxlength="30" placeholder="mis. 045" oninput="previewNomor()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_surat" required oninput="previewNomor()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <p class="sm:col-span-2 text-[11px] text-sky-900">No. di surat: <strong id="previewNomor" class="font-mono"></strong></p>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Ditujukan kepada (Bapak/Ibu ...) <span class="text-rose-500">*</span></label>
                        <input type="text" name="ditujukan_kepada" required maxlength="150" placeholder="mis. Kaprodi Teknik Informatika Universitas Tidar" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Pengajuan dari Kampus/Sekolah <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_pengajuan" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Media Pengajuan (melalui Surat ...) <span class="text-rose-500">*</span></label>
                        <input type="text" name="media_pengajuan" required maxlength="100" list="daftarMediaPengajuan" placeholder="mis. Elektronik (Email)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <datalist id="daftarMediaPengajuan">
                            <option value="Elektronik (Email)">
                            <option value="Elektronik (Portal Lowongan)">
                            <option value="Resmi">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pelaksanaan Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_mulai" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pelaksanaan Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_selesai" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Induction / Training Introduction (pukul {{ config('prakerin.surat_balasan.jam_induction') }} WIB) <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_induction" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <p class="text-[11px] text-sky-900 leading-relaxed">
                    Nama peserta, NIS/NIM, program studi, dan asal kampus/sekolah diambil otomatis dari data lamaran.
                    Penandatangan: {{ config('prakerin.surat_balasan.penandatangan') }}, {{ config('prakerin.surat_balasan.jabatan') }}.
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="tampilPanel(null)" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white transition">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 transition">Simpan Surat Balasan</button>
                </div>
            </form>
        </div>

        <div id="footerAksi" class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
            <button type="button" onclick="closeApplicantModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Tutup</button>
            <a id="btnSuratPdf" href="#" target="_blank" rel="noopener" class="hidden px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition items-center gap-1">
                <i class="fa-solid fa-file-pdf text-rose-500"></i> Lihat PDF Surat
            </a>
            <button id="btnSurat" type="button" onclick="tampilPanel('surat')" class="hidden px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue transition items-center gap-1 shadow-sm">
                <i class="fa-solid fa-file-signature"></i> <span id="btnSuratLabel">Buat Surat Balasan</span>
            </button>
            <button id="btnTolak" type="button" onclick="tampilPanel('tolak')" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition flex items-center gap-1">
                <i class="fa-solid fa-xmark"></i> Tolak
            </button>
            <button id="btnTerima" type="button" onclick="tampilPanel('terima')" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-blue to-brand-purpleBlue transition flex items-center gap-1 shadow-sm">
                <i class="fa-solid fa-check"></i> Terima Peserta
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const applicants = @json($rows);
    const urlTerima = @json(route('admin.lamaran.terima', ['lamaran' => '__ID__']));
    const urlTolak  = @json(route('admin.lamaran.tolak', ['lamaran' => '__ID__']));
    const $ = (id) => document.getElementById(id);

    function linkBerkas(url, label) {
        if (!url) return '<span class="text-slate-400 italic">Tidak dilampirkan</span>';
        return '<a href="' + url + '" target="_blank" rel="noopener" class="text-brand-blue font-bold hover:underline">' + label + '</a>';
    }

    const PANEL = { terima: 'panelTerima', tolak: 'panelTolak', surat: 'panelSurat' };
    const ROMAWI = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    const KODE_NOMOR = @json(config('prakerin.surat_balasan.kode_nomor'));

    function tampilPanel(nama) {
        Object.entries(PANEL).forEach(([k, id]) => $(id).classList.toggle('hidden', k !== nama));
        if (nama) $(PANEL[nama]).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Pratinjau nomor surat: <nomor>/PRAKERIN/HCD/MAJ/<bulan romawi>/<tahun>
    function previewNomor() {
        const f = $('panelSurat').elements;
        const tgl = (f.tanggal_surat.value || '').split('-');
        const bulan = ROMAWI[parseInt(tgl[1], 10)] || '..';
        $('previewNomor').textContent = (f.nomor_surat.value || '...') + '/' + KODE_NOMOR + '/' + bulan + '/' + (tgl[0] || '....');
    }

    function openDetailModal(id) {
        const app = applicants.find(a => a.id === id);
        if (!app) return;

        $('modalApplicantName').textContent = app.nama;
        $('modalApplicantPosition').textContent = 'Posisi Dilamar: ' + app.posisi;
        $('modalApplicantBadge').textContent = app.smk ? 'Anak SMK' : 'Diploma / Sarjana';
        $('modalApplicantBadge').className = 'inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase mb-1 text-slate-900 '
            + (app.smk ? 'bg-amber-400' : 'bg-sky-300');

        $('mEmail').textContent = app.email;
        $('mWa').textContent = app.wa;
        $('mAsalLabel').textContent = app.asalLabel + ':';
        $('mAsal').textContent = app.asal;
        $('mJurusan').textContent = app.jurusan;
        $('mAlamat').textContent = app.alamat;
        $('mPeriode').textContent = app.periode;
        $('mPembimbingLabel').textContent = app.pembimbingLabel + ':';
        $('mPembimbing').textContent = app.pembimbing;
        $('mTglDaftar').textContent = app.tglDaftar;

        $('berkasFoto').innerHTML = linkBerkas(app.berkas.foto, 'Lihat Foto');
        $('berkasCv').innerHTML = linkBerkas(app.berkas.cv, 'Lihat Dokumen');
        $('berkasSurat').innerHTML = linkBerkas(app.berkas.surat, 'Lihat Dokumen');

        const menunggu = app.status === 'menunggu';
        $('footerAksi').querySelectorAll('#btnTolak, #btnTerima').forEach(b => b.classList.toggle('hidden', !menunggu));

        // Surat balasan: hanya untuk lamaran yang sudah diterima
        const diterima = app.status === 'diterima';
        $('btnSurat').classList.toggle('hidden', !diterima);
        $('btnSuratLabel').textContent = app.adaSurat ? 'Ubah Surat Balasan' : 'Buat Surat Balasan';
        $('btnSuratPdf').classList.toggle('hidden', !(diterima && app.suratPdf));
        if (app.suratPdf) $('btnSuratPdf').href = app.suratPdf;
        $('mSuratWrap').classList.toggle('hidden', !diterima);
        $('mSurat').textContent = app.adaSurat ? 'Sudah dibuat' : 'Belum dibuat';
        $('panelSurat').action = app.suratUrl;
        $('suratLamaranId').value = app.id;
        Object.entries(app.surat).forEach(([k, v]) => { if ($('panelSurat').elements[k]) $('panelSurat').elements[k].value = v; });
        previewNomor();
        $('blokKeputusan').classList.toggle('hidden', menunggu);
        if (!menunggu) {
            $('mStatus').textContent = app.status === 'diterima' ? 'Diterima' : 'Ditolak';
            $('mDiputuskan').textContent = app.diputuskan || '-';
            $('mCatatanWrap').classList.toggle('hidden', !app.catatan);
            $('mCatatan').textContent = app.catatan || '';
        }

        $('panelTerima').action = urlTerima.replace('__ID__', app.id);
        $('panelTolak').action = urlTolak.replace('__ID__', app.id);
        $('selDepartemen').value = app.departemenId || '';
        tampilPanel(null);

        $('applicantDetailModal').classList.remove('hidden');
    }

    function closeApplicantModal() {
        $('applicantDetailModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if (request()->filled('buka'))
            openDetailModal({{ (int) request('buka') }});
            @if (request()->boolean('surat'))
                // Baru saja diterima: langsung tampilkan form surat balasan
                tampilPanel('surat');
            @endif
        @endif

        @if ($errors->any() && old('_surat_lamaran'))
            // Validasi form surat gagal: buka lagi modalnya dengan isian yang tadi
            openDetailModal({{ (int) old('_surat_lamaran') }});
            Object.entries(@json(old())).forEach(([k, v]) => {
                const el = $('panelSurat').elements[k];
                if (el && k !== '_token' && k !== '_surat_lamaran') el.value = v;
            });
            previewNomor();
            tampilPanel('surat');
        @endif
    });
</script>
@endpush