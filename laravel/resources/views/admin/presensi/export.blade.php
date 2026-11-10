@extends('layouts.admin')
@section('content')
@php
  $inp = 'border border-slate-200 rounded-lg px-3 py-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200';
  $kolom = [
    ['TAHUN',                'Tahun rekap',                                  '2026'],
    ['NO BULAN',             'Nomor bulan rekap',                            '09'],
    ['BULAN',                'Nama bulan rekap',                             'SEPTEMBER'],
    ['NAMA',                 'Nama peserta',                                 'ALANA RAYA PRATAMA'],
    ['SEKOLAH / UNIVERSITAS','Asal sekolah / kampus',                        'SMK 1 MAGELANG'],
    ['DEPARTEMEN',           'Departemen penempatan',                        'IRM'],
    ['01 – 31',              'Satu kolom per tanggal: 1 = masuk, 0 = tidak', '1'],
    ['JML',                  'Jumlah hari masuk (rumus otomatis)',           '19'],
    ['%',                    'JML ÷ hari kerja (rumus otomatis)',            '100%'],
    ['HK',                   'Jumlah hari kerja di bulan itu (sama semua)',  '14'],
  ];
  $legenda = [
    ['bg-[#A9D08E] text-slate-800', '1',  'Masuk (hadir)'],
    ['bg-[#FFFF00] text-slate-800', '0',  'Tidak masuk (izin, sakit, cuti, alpha, atau tanpa data)'],
    ['bg-slate-200 text-slate-400', '▨',  'Sabtu, Minggu, dan hari libur (Kalender Kerja)'],
    ['bg-white border border-slate-300 text-slate-300', '', 'Di luar masa magang peserta'],
  ];
  $alur = [
    ['Pilih bulan', 'Satu file berisi satu bulan penuh.'],
    ['Pilih departemen', 'Kosongkan untuk semua departemen.'],
    ['Hitung kehadiran', 'Status tiap tanggal diubah menjadi 1 atau 0.'],
    ['Download Excel', 'File .xlsx siap dibuka di Excel.'],
  ];
@endphp

<div class="max-w-6xl">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold">Export Rekap Presensi</h1>
      <p class="text-xs text-slate-500 mt-1">Unduh rekap absensi prakerin per bulan dalam format Excel: hanya masuk (1) atau tidak masuk (0).</p>
    </div>
    <a href="{{ route('admin.presensi.index') }}" class="shrink-0 border border-slate-300 hover:bg-slate-50 text-xs font-semibold rounded-lg px-4 py-2.5">‹ Kembali ke Presensi</a>
  </div>

  {{-- Pesan --}}
  @if(session('error'))
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">
      <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="grid lg:grid-cols-3 gap-5 mt-6">

    {{-- Kiri --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Pilih periode --}}
      <form method="GET" action="{{ route('admin.presensi.export') }}" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-slate-100 grid place-items-center">📅</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Pilih Periode Rekap</h2>
            <p class="text-[11px] text-slate-500">Tentukan bulan dan departemen yang akan diexport</p>
          </div>
        </div>

        <div class="border-2 border-dashed border-slate-300 bg-slate-50 rounded-xl p-6">
          <div class="grid sm:grid-cols-2 gap-4">
            <label class="text-[11px] font-semibold text-slate-700">Bulan
              <input type="month" name="bulan" value="{{ old('bulan', $bulanDefault) }}" required class="{{ $inp }} w-full mt-1">
            </label>
            <label class="text-[11px] font-semibold text-slate-700">Departemen
              <select name="departemen" class="{{ $inp }} w-full mt-1">
                <option value="">Semua Departemen</option>
                @foreach($departemen as $d)<option @selected(old('departemen') === $d)>{{ $d }}</option>@endforeach
              </select>
            </label>
          </div>
          <div class="flex gap-2 mt-4">
            @foreach(['XLSX'] as $f)<span class="text-[10px] font-semibold bg-slate-200/70 text-slate-600 rounded-full px-3 py-1">{{ $f }}</span>@endforeach
            <span class="text-[10px] font-semibold bg-slate-200/70 text-slate-600 rounded-full px-3 py-1">1 BULAN / FILE</span>
          </div>
        </div>

        <button type="submit" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg py-3">⇩ Download Rekap Excel</button>
      </form>

      {{-- Struktur Excel --}}
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-green-50 grid place-items-center">▦</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Struktur Excel</h2>
            <p class="text-[11px] text-slate-500">Judul "REKAP ABSENSI PRAKERIN", lalu satu baris per peserta</p>
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
          <div class="w-9 h-9 rounded-lg bg-slate-100 grid place-items-center">🎨</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Arti Warna Sel</h2>
            <p class="text-[11px] text-slate-500">Cara membaca kolom tanggal</p>
          </div>
        </div>
        <ul class="space-y-3">
          @foreach($legenda as [$kelas, $isi, $teks])
            <li class="flex items-start gap-3">
              <span class="w-7 h-7 rounded-md text-xs font-bold grid place-items-center shrink-0 {{ $kelas }}">{{ $isi }}</span>
              <span class="text-[11px] text-slate-600 pt-1">{{ $teks }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-lg bg-amber-50 grid place-items-center">⚙</div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Alur Export</h2>
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
          <div class="text-[11px] font-bold text-amber-900">Catatan perhitungan</div>
          <p class="text-[10px] text-amber-800 mt-1">Hari kerja (HK) sama untuk semua peserta: seluruh Senin–Jumat di bulan itu tanpa hari libur. Untuk bulan berjalan, hanya sampai hari ini. Tanggal di luar masa magang peserta dibiarkan putih dan tetap dihitung sebagai tidak hadir pada persentase.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection