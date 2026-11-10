@extends('layouts.admin')
@section('content')
@php
  $inp = 'w-full border border-slate-200 rounded-lg px-3 py-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200';
  $lbl = 'block text-[11px] font-semibold text-slate-700 mb-1';
  $err = fn ($f) => $errors->first($f);
@endphp

<div class="max-w-4xl">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold">Tambah Anak Magang</h1>
      <p class="text-xs text-slate-500 mt-1">Daftarkan peserta baru. Akun login dibuat otomatis dan QR kartu langsung tersedia.</p>
    </div>
    <a href="{{ route('admin.presensi.index') }}" class="shrink-0 border border-slate-300 hover:bg-slate-50 text-xs font-semibold rounded-lg px-4 py-2.5">← Kembali</a>
  </div>

  {{-- Info akun --}}
  <div class="mt-4 rounded-lg bg-blue-50 border border-blue-100 text-blue-800 text-xs px-4 py-3 leading-relaxed">
    <b>Akun login peserta:</b> login memakai <b>NIS/NIM</b>, kata sandi awal = <b>{{ \App\Models\User::PASSWORD_DEFAULT }}</b>.
    Peserta wajib mengganti kata sandi saat login pertama.
  </div>

  {{-- Error --}}
  @if($errors->any())
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">
      <b>Data belum dapat disimpan:</b>
      <ul class="list-disc pl-4 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.peserta.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
    @csrf

    {{-- Data peserta --}}
    <section class="border border-slate-200 rounded-xl shadow-sm p-5">
      <h2 class="text-sm font-bold mb-4">Data Peserta</h2>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="{{ $lbl }}">Nama Lengkap <span class="text-red-500">*</span></label>
          <input name="nama" value="{{ old('nama') }}" required class="{{ $inp }} @if($err('nama')) border-red-300 @endif">
        </div>
        <div>
          <label class="{{ $lbl }}">NIS / NIM <span class="text-red-500">*</span></label>
          <input name="nis_nim" value="{{ old('nis_nim') }}" required placeholder="Dipakai peserta untuk login" class="{{ $inp }} @if($err('nis_nim')) border-red-300 @endif">
        </div>
        <div>
          <label class="{{ $lbl }}">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="{{ $inp }}">
            <option value="">—</option>
            <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
          </select>
        </div>
        <div>
          <label class="{{ $lbl }}">Tipe Magang <span class="text-red-500">*</span></label>
          <select name="tipe_magang" required class="{{ $inp }}">
            <option value="smk" @selected(old('tipe_magang', 'smk') === 'smk')>SMK (Prakerin)</option>
            <option value="kuliah" @selected(old('tipe_magang') === 'kuliah')>Kuliah</option>
          </select>
        </div>
        <div>
          <label class="{{ $lbl }}">Email <span class="font-normal text-slate-400">(opsional)</span></label>
          <input type="email" name="email" value="{{ old('email') }}" class="{{ $inp }} @if($err('email')) border-red-300 @endif">
        </div>
        <div>
          <label class="{{ $lbl }}">No. WhatsApp <span class="font-normal text-slate-400">(opsional)</span></label>
          <input name="no_wa" value="{{ old('no_wa') }}" inputmode="tel" class="{{ $inp }}">
        </div>
      </div>
    </section>

    {{-- Pendidikan --}}
    <section class="border border-slate-200 rounded-xl shadow-sm p-5">
      <h2 class="text-sm font-bold mb-4">Asal Pendidikan</h2>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="{{ $lbl }}">Universitas / Sekolah</label>
          <input name="asal_sekolah" value="{{ old('asal_sekolah') }}" class="{{ $inp }}">
        </div>
        <div>
          <label class="{{ $lbl }}">Prodi / Jurusan</label>
          <input name="jurusan" value="{{ old('jurusan') }}" class="{{ $inp }}">
        </div>
      </div>
    </section>

    {{-- Penempatan & periode --}}
    <section class="border border-slate-200 rounded-xl shadow-sm p-5">
      <h2 class="text-sm font-bold mb-4">Penempatan &amp; Periode</h2>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="{{ $lbl }}">Departemen <span class="text-red-500">*</span></label>
          <select name="departemen_id" required class="{{ $inp }} @if($err('departemen_id')) border-red-300 @endif">
            <option value="">Pilih departemen</option>
            @foreach($departemen as $d)
              <option value="{{ $d->id }}" @selected((string) old('departemen_id') === (string) $d->id)>{{ $d->nama }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="{{ $lbl }}">Divisi</label>
          <input name="divisi" value="{{ old('divisi') }}" class="{{ $inp }}">
        </div>
        <div class="md:col-span-2">
          <label class="{{ $lbl }}">Tempat Prakerin</label>
          <input name="tempat_prakerin" value="{{ old('tempat_prakerin') }}" class="{{ $inp }}">
        </div>
        <div>
          <label class="{{ $lbl }}">Tanggal Mulai <span class="text-red-500">*</span></label>
          <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai') }}" required class="{{ $inp }} @if($err('tgl_mulai')) border-red-300 @endif">
          <p class="text-[10px] text-slate-400 mt-1">Presensi dihitung sejak tanggal ini.</p>
        </div>
        <div>
          <label class="{{ $lbl }}">Tanggal Selesai <span class="text-red-500">*</span></label>
          <input type="date" name="tgl_selesai" value="{{ old('tgl_selesai') }}" required class="{{ $inp }} @if($err('tgl_selesai')) border-red-300 @endif">
        </div>
      </div>
    </section>

    {{-- Tambahan --}}
    <details class="border border-slate-200 rounded-xl shadow-sm p-5" @if($errors->hasAny(['no_ktp','tanggal_lahir','foto_pas','cv_path','surat_permohonan_path'])) open @endif>
      <summary class="text-sm font-bold cursor-pointer">Data Tambahan <span class="font-normal text-slate-400 text-xs">(opsional, bisa dilengkapi nanti lewat Edit Peserta)</span></summary>
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="{{ $lbl }}">Pembimbing</label>
          <input name="pembimbing" value="{{ old('pembimbing') }}" class="{{ $inp }}">
        </div>
        <div>
          <label class="{{ $lbl }}">WhatsApp Pembimbing</label>
          <input name="no_wa_pembimbing" value="{{ old('no_wa_pembimbing') }}" inputmode="tel" class="{{ $inp }}">
        </div>
        <div>
          <label class="{{ $lbl }}">No. KTP</label>
          <input name="no_ktp" value="{{ old('no_ktp') }}" inputmode="numeric" maxlength="16" placeholder="16 digit" class="{{ $inp }} @if($err('no_ktp')) border-red-300 @endif">
        </div>
        <div>
          <label class="{{ $lbl }}">Tempat Lahir</label>
          <input name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="{{ $inp }}">
        </div>
        <div>
          <label class="{{ $lbl }}">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="{{ $inp }} @if($err('tanggal_lahir')) border-red-300 @endif">
        </div>
        <div>
          <label class="{{ $lbl }}">Keterangan</label>
          <input name="keterangan" value="{{ old('keterangan') }}" class="{{ $inp }}">
        </div>
        <div class="md:col-span-2">
          <label class="{{ $lbl }}">Alamat</label>
          <textarea name="alamat" rows="2" class="{{ $inp }}">{{ old('alamat') }}</textarea>
        </div>
        <div>
          <label class="{{ $lbl }}">Foto Pas (JPG/PNG, maks. 2MB)</label>
          <input type="file" name="foto_pas" accept="image/png,image/jpeg" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
        </div>
        <div>
          <label class="{{ $lbl }}">CV (PDF/DOC, maks. 5MB)</label>
          <input type="file" name="cv_path" accept=".pdf,.doc,.docx" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
        </div>
        <div class="md:col-span-2">
          <label class="{{ $lbl }}">Surat Permohonan (PDF/JPG/PNG, maks. 5MB)</label>
          <input type="file" name="surat_permohonan_path" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
        </div>
      </div>
    </details>

    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.presensi.index') }}" class="border border-slate-300 hover:bg-slate-50 text-xs font-semibold rounded-lg px-5 py-2.5">Batal</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg px-5 py-2.5">Simpan Peserta</button>
    </div>
  </form>
</div>
@endsection