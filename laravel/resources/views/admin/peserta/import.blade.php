@extends('layouts.admin')
@section('content')
@php
  $kolom = [
    ['NIS/NIM',         'NIS/NIM peserta (wajib)',           '2401086'],
    ['NAMA',            'Nama lengkap (wajib)',                'Contoh Peserta'],
    ['UNIV/SEKOLAH',    'Asal universitas / sekolah',          'SMK Contoh'],
    ['PRODI',           'Program studi / jurusan',             'Teknik Informatika'],
    ['DEPARTEMEN',      'Nama departemen penempatan',          'IT'],
    ['DIVISI',          'Divisi di departemen',                'Development'],
    ['UUID',            'Kosongkan, dibuat otomatis oleh sistem', '-'],
    ['L/P',             'Jenis kelamin: L atau P',             'L'],
    ['TGL_MULAI',       'Tanggal mulai magang',                '2026-10-01'],
    ['TGL_SELESAI',     'Tanggal selesai magang',              '2026-12-31'],
    ['KET',             'Catatan tambahan',                    'Magang reguler'],
    ['TEMPAT PRAKERIN', 'Nama perusahaan / tempat',            'PT Mekar Armada Jaya'],
    ['JENJANG',         'SMK atau KULIAH (kosong = ditebak dari nama sekolah/kampus)', 'SMK'],
  ];
  $tanpaAkun = \App\Models\DataMagang::whereNull('user_id')->whereNotNull('nis_nim')->count();
  $alur = [
    ['Upload Excel', 'File dibaca oleh sistem.'],
    ['Validasi data', 'NIS/NIM dan nama diperiksa.'],
    ['Buat peserta', 'Data dihubungkan ke akun.'],
    ['Simpan', 'Data disimpan ke database.'],
  ];
@endphp

<div class="max-w-6xl" x-data="{ nama: '', drag: false, kirim: false,
    pilih(f){ if(!f) return; this.nama = f.name; },
    drop(e){ this.drag=false; const f=e.dataTransfer.files[0]; if(!f) return;
             this.$refs.file.files = e.dataTransfer.files; this.pilih(f); } }">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold">Import Peserta Magang</h1>
      <p class="text-xs text-slate-500 mt-1">Tambah banyak peserta sekaligus menggunakan file Excel.</p>
    </div>
    <a href="{{ route('admin.peserta.template') }}" class="shrink-0 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg px-4 py-2.5">⇩ Download Template</a>
  </div>

  {{-- Pesan --}}
  @if(session('success'))
    <div class="mt-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-xs px-4 py-3">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">
      <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif
  @if(!empty(session('import_errors')))
    <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs px-4 py-3">
      <b>Catatan import:</b>
      <ul class="list-disc pl-4 mt-1 max-h-40 overflow-y-auto">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @if($tanpaAkun > 0)
    <div class="mt-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs px-4 py-3 flex items-center justify-between gap-3">
      <span><b>{{ $tanpaAkun }}</b> data peserta belum punya akun login, jadi belum muncul di tabel Presensi dan Akun Peserta. Sandi awal 12345678, wajib diganti saat login pertama.</span>
      <form method="POST" action="{{ route('admin.peserta.buat-akun') }}" onsubmit="return confirm('Buat akun login untuk {{ $tanpaAkun }} peserta?')">
        @csrf
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-4 py-2 whitespace-nowrap">Buat akun sekarang</button>
      </form>
    </div>
  @endif

  <div class="grid lg:grid-cols-3 gap-5 mt-6">

    {{-- Kiri --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Upload --}}
      <form method="POST" action="{{ route('admin.peserta.import') }}" enctype="multipart/form-data"
            @submit="kirim = true"
            class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        @csrf
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-slate-100 grid place-items-center">📄</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Upload File Excel</h2>
            <p class="text-[11px] text-slate-500">Pilih file data peserta yang akan diimport</p>
          </div>
        </div>

        <label @dragover.prevent="drag = true" @dragleave.prevent="drag = false" @drop.prevent="drop($event)"
               :class="drag ? 'border-blue-400 bg-blue-50' : 'border-slate-300 bg-slate-50'"
               class="flex flex-col items-center justify-center text-center gap-2 border-2 border-dashed rounded-xl py-14 cursor-pointer transition">
          <div class="w-12 h-12 rounded-xl bg-blue-600 text-white grid place-items-center text-xl">⇧</div>
          <div class="text-xs font-bold text-slate-900" x-text="nama || 'Pilih File Excel'"></div>
          <div class="text-[11px] text-slate-500" x-show="!nama">Klik area ini atau tarik file ke sini</div>
          <div class="flex gap-2 mt-1">
            @foreach(['XLSX','XLS','CSV'] as $f)<span class="text-[10px] font-semibold bg-slate-200/70 text-slate-600 rounded-full px-3 py-1">{{ $f }}</span>@endforeach
          </div>
          <input x-ref="file" type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required
                 @change="pilih($event.target.files[0])">
        </label>

        <button type="submit" :disabled="!nama || kirim"
                class="mt-4 w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg py-3">
          <span x-text="kirim ? 'Memproses…' : 'Mulai Import'"></span>
        </button>
      </form>

      {{-- Struktur Excel --}}
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-green-50 grid place-items-center">▦</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Struktur Excel</h2>
            <p class="text-[11px] text-slate-500">Format kolom yang digunakan saat import (baris pertama = judul kolom)</p>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="text-left text-slate-900 border-b border-slate-200">
                <th class="py-2 pr-3 w-10">#</th><th class="py-2 pr-3">Kolom</th><th class="py-2 pr-3">Keterangan</th><th class="py-2">Contoh</th>
              </tr>
            </thead>
            <tbody class="text-slate-600">
              @foreach($kolom as $i => [$k, $ket, $contoh])
                <tr class="border-b border-slate-100">
                  <td class="py-2 pr-3">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                  <td class="py-2 pr-3 font-bold text-slate-900">{{ $k }}</td>
                  <td class="py-2 pr-3">{{ $ket }}</td>
                  <td class="py-2">{{ $contoh }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Kanan --}}
    <div class="space-y-5">
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-slate-100 grid place-items-center">🔗</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Mapping NIS/NIM</h2>
            <p class="text-[11px] text-slate-500">Tidak perlu mengisi UUID</p>
          </div>
        </div>
        <div class="bg-slate-100 rounded-xl p-4">
          <div class="flex items-center justify-center gap-4 text-[10px] font-bold">
            <span class="text-slate-600">NIS/NIM</span>
            <span class="text-green-600">PESERTA</span>
            <span class="bg-amber-100 text-amber-700 rounded-full px-3 py-1">QR KARTU</span>
          </div>
          <p class="text-[10px] text-slate-500 mt-3">Cukup isi <b>NIS/NIM</b> dan <b>nama</b>. UUID untuk QR kartu dibuat otomatis oleh sistem.</p>
        </div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-lg bg-amber-50 grid place-items-center">⚙</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Alur Import</h2>
            <p class="text-[11px] text-slate-500">Proses otomatis sistem</p>
          </div>
        </div>
        <ol class="space-y-4">
          @foreach($alur as $i => [$j, $d])
            <li class="flex items-start gap-3">
              <span class="w-6 h-6 rounded-md text-white text-[10px] font-bold grid place-items-center shrink-0 {{ $loop->last ? 'bg-green-600' : 'bg-blue-600' }}">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
              <div><div class="text-[11px] font-bold text-slate-900">{{ $j }}</div><div class="text-[10px] text-slate-500">{{ $d }}</div></div>
            </li>
          @endforeach
        </ol>
      </div>

      <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-100 grid place-items-center shrink-0">⚠</div>
        <div>
          <div class="text-[11px] font-bold text-amber-900">Periksa file sebelum upload</div>
          <p class="text-[10px] text-amber-800 mt-1">Pakai template resmi dan jangan ubah judul kolom. Baris yang bermasalah akan dilaporkan setelah import selesai.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection