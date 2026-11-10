@extends('layouts.admin')
@section('title', 'Edit Peserta')
@section('content')

@php
    $inp = 'w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400';
    $lbl = 'block text-xs font-semibold text-slate-700 mb-1.5';
    $tgl = fn($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
    // Berkas disajikan lewat rute admin.peserta.berkas (bukan /storage), supaya tidak 404 / rusak.
    $fotoSaatIni = $dm->foto_pas ? route('admin.peserta.berkas', [$peserta, 'foto', 'v' => substr(md5($dm->foto_pas), 0, 8)]) : $peserta->foto_profil_url;
@endphp

<div class="max-w-4xl mx-auto">

    {{-- HEADER --}}
    <div class="mb-6">
        <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center text-xs text-slate-500 hover:text-blue-600 mb-3">← Kembali</a>
        <h1 class="text-2xl font-extrabold text-slate-800">Edit Peserta</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $peserta->name }} · NIS/NIM {{ $peserta->nis_nim ?? '—' }}</p>
    </div>

    <form method="POST" action="{{ route('admin.peserta.update', $peserta) }}" enctype="multipart/form-data"
          class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        @csrf
        @method('PUT')

        @unless($dm->exists)
            <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
                Akun ini dibuat lewat pendaftaran dan belum punya data magang. Saat form ini disimpan,
                data magang dibuat otomatis.
            </div>
        @endunless

        @if($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                <div class="font-semibold mb-1">Data belum dapat disimpan:</div>
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- DATA PRIBADI --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h2 class="text-sm font-bold text-slate-800">Data Pribadi</h2></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $dm->nama ?? $peserta->name) }}" class="{{ $inp }}" required>
                </div>
                <div>
                    <label class="{{ $lbl }}">Email</label>
                    <input type="email" name="email" value="{{ old('email', $peserta->email) }}" class="{{ $inp }}">
                    <p class="text-[11px] text-slate-400 mt-1">Dikosongkan = email tidak diubah.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">No. KTP (NIK)</label>
                    <input type="text" name="no_ktp" value="{{ old('no_ktp', $dm->no_ktp) }}" maxlength="16" inputmode="numeric" class="{{ $inp }}" placeholder="16 digit">
                </div>
                <div>
                    <label class="{{ $lbl }}">No. WhatsApp</label>
                    <input type="text" name="no_wa" value="{{ old('no_wa', $dm->no_wa) }}" class="{{ $inp }}" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="{{ $lbl }}">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="{{ $inp }}">
                        <option value="">—</option>
                        <option value="L" @selected(old('jenis_kelamin', $dm->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $dm->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $dm->tempat_lahir) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $tgl($dm->tanggal_lahir)) }}" class="{{ $inp }}">
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $lbl }}">Alamat</label>
                    <textarea name="alamat" rows="2" class="{{ $inp }}">{{ old('alamat', $dm->alamat) }}</textarea>
                </div>
            </div>
        </div>

        {{-- PENDIDIKAN --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h2 class="text-sm font-bold text-slate-800">Pendidikan</h2></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}">Tipe Magang <span class="text-red-500">*</span></label>
                    <select name="tipe_magang" class="{{ $inp }}" required>
                        <option value="smk" @selected(old('tipe_magang', $dm->tipe_magang) === 'smk')>SMK</option>
                        <option value="kuliah" @selected(old('tipe_magang', $dm->tipe_magang) === 'kuliah')>Kuliah</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">NIS / NIM</label>
                    <input type="text" name="nis_nim" value="{{ old('nis_nim', $dm->nis_nim) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Asal Sekolah / Kampus</label>
                    <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah', $dm->asal_sekolah) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Jurusan</label>
                    <input type="text" name="jurusan" value="{{ old('jurusan', $dm->jurusan) }}" class="{{ $inp }}">
                </div>
            </div>
        </div>

        {{-- PENEMPATAN --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h2 class="text-sm font-bold text-slate-800">Penempatan & Pembimbing</h2></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}">Departemen <span class="text-red-500">*</span></label>
                    <select name="departemen_id" class="{{ $inp }}" required>
                        <option value="">Pilih departemen</option>
                        @foreach($departemen as $d)
                            <option value="{{ $d->id }}" @selected(old('departemen_id', $dm->departemen_id ?? $peserta->departemen_id) == $d->id)>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">Divisi</label>
                    <input type="text" name="divisi" value="{{ old('divisi', $dm->divisi) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Tempat Prakerin</label>
                    <input type="text" name="tempat_prakerin" value="{{ old('tempat_prakerin', $dm->tempat_prakerin) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Pembimbing</label>
                    <input type="text" name="pembimbing" value="{{ old('pembimbing', $dm->pembimbing) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">No. WhatsApp Pembimbing</label>
                    <input type="text" name="no_wa_pembimbing" value="{{ old('no_wa_pembimbing', $dm->no_wa_pembimbing) }}" class="{{ $inp }}">
                </div>
            </div>
        </div>

        {{-- PERIODE --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h2 class="text-sm font-bold text-slate-800">Periode Magang</h2></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}">Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $tgl($dm->tgl_mulai)) }}" class="{{ $inp }}">
                    <p class="text-[11px] text-slate-400 mt-1">Dipakai untuk menghitung persentase kehadiran.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $tgl($dm->tgl_selesai)) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Periode (teks bebas)</label>
                    <input type="text" name="periode_magang" value="{{ old('periode_magang', $dm->periode_magang) }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan', $dm->keterangan) }}" class="{{ $inp }}">
                </div>
            </div>
        </div>

        {{-- BERKAS --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h2 class="text-sm font-bold text-slate-800">Berkas</h2></div>
            <p class="text-[11px] text-slate-400 -mt-2 mb-3">Kosongkan jika tidak ingin mengganti berkas.</p>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $lbl }}">Foto Pas (jpg/png, maks 2 MB)</label>
                    @if($fotoSaatIni)<img src="{{ $fotoSaatIni }}" alt="" class="w-16 h-16 rounded-lg object-cover mb-2">@endif
                    <input type="file" name="foto_pas" accept=".jpg,.jpeg,.png" class="text-xs w-full">
                </div>
                <div>
                    <label class="{{ $lbl }}">CV (pdf/doc/docx, maks 5 MB)</label>
                    @if($dm->cv_path)<a href="{{ route('admin.peserta.berkas', [$peserta, 'cv']) }}" target="_blank" class="block text-xs text-blue-600 hover:underline mb-2">Lihat CV saat ini</a>@endif
                    <input type="file" name="cv_path" accept=".pdf,.doc,.docx" class="text-xs w-full">
                </div>
                <div>
                    <label class="{{ $lbl }}">Surat Permohonan (pdf/jpg/png, maks 5 MB)</label>
                    @if($dm->surat_permohonan_path)<a href="{{ route('admin.peserta.berkas', [$peserta, 'surat']) }}" target="_blank" class="block text-xs text-blue-600 hover:underline mb-2">Lihat surat saat ini</a>@endif
                    <input type="file" name="surat_permohonan_path" accept=".pdf,.jpg,.jpeg,.png" class="text-xs w-full">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.presensi.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm">Simpan Perubahan</button>
        </div>
    </form>
</div>

@endsection