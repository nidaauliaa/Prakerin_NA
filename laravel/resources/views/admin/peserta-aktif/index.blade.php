@extends('layouts.admin')
@section('title','Peserta Aktif')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<div x-data="pesertaAktif()" @if($qrBaru) x-init="bukaQr(@js($qrBaru))" @endif @keydown.escape.window="modal = null">

<div class="flex items-start justify-between gap-4">
<div>
<h1 class="text-2xl font-extrabold">Peserta Aktif</h1>
<p class="text-xs text-slate-500 mt-1">Data magang yang masih berjalan: status ACTIVE dan tanggal selesai magang belum lewat.</p>
</div>
<a href="{{ route('admin.peserta.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg px-4 py-2.5 shrink-0">+ Tambah Anak Magang</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
@foreach([['Peserta aktif',$stat['aktif'],'text-blue-600'],['Sudah punya akun login',$stat['punya_akun'],'text-green-600'],['Belum punya akun',$stat['belum_akun'],'text-amber-600'],['Selesai ≤ 30 hari lagi',$stat['segera_selesai'],'text-red-600']] as [$l,$v,$c])
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm">
<div class="text-2xl font-bold {{ $c }}">{{ $v }}</div>
<div class="text-[11px] text-slate-500">{{ $l }}</div>
</div>
@endforeach
</div>

<form method="GET" action="{{ route('admin.aktif.index') }}" class="flex flex-wrap gap-2 mt-5">
<input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS / NIM" class="{{ $inp }}">
<select name="departemen_id" class="{{ $inp }}">
<option value="">Semua departemen</option>
@foreach($departemen as $d)<option value="{{ $d->id }}" @selected(request('departemen_id')==$d->id)>{{ $d->nama }}</option>@endforeach
</select>
<select name="tipe" class="{{ $inp }}">
<option value="">SMK &amp; Kuliah</option>
<option value="smk" @selected(request('tipe')==='smk')>SMK</option>
<option value="kuliah" @selected(request('tipe')==='kuliah')>Kuliah</option>
</select>
<button class="bg-slate-800 text-white text-xs font-semibold rounded-lg px-4">Filter</button>
<a href="{{ route('admin.aktif.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Peserta</th>
<th class="px-4 py-3">Sekolah / Kampus</th>
<th class="px-4 py-3">Departemen</th>
<th class="px-4 py-3">Periode magang</th>
<th class="px-4 py-3">Pembimbing</th>
<th class="px-4 py-3">Akun</th>
<th class="px-4 py-3">Zona</th>
<th class="px-4 py-3"></th>
</tr></thead>
<tbody>
@forelse($data as $d)
@php
  $sisa = $d->tgl_selesai ? (int) today()->diffInDays($d->tgl_selesai->copy()->startOfDay(), false) : null;
  $zn = $d->user?->zona();
@endphp
<tr class="border-t border-slate-100 align-top">
<td class="px-4 py-3">
<div class="font-semibold">{{ $d->nama }}</div>
<div class="text-[11px] text-slate-500">{{ $d->nis_nim ?: '—' }}
<span class="ml-1 rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[10px] font-semibold uppercase">{{ $d->tipe_magang }}</span></div>
</td>
<td class="px-4 py-3">{{ $d->asal_sekolah ?: '—' }}<div class="text-[11px] text-slate-500">{{ $d->jurusan }}</div></td>
<td class="px-4 py-3">{{ optional($d->departemen)->nama ?? '—' }}<div class="text-[11px] text-slate-500">{{ $d->divisi }}</div></td>
<td class="px-4 py-3 whitespace-nowrap">
{{ $d->tgl_mulai?->translatedFormat('d M Y') ?? '—' }} – {{ $d->tgl_selesai?->translatedFormat('d M Y') ?? '—' }}
@if($sisa !== null)
<div class="mt-0.5"><span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $sisa <= 30 ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">{{ $sisa === 0 ? 'Hari terakhir' : 'Sisa '.$sisa.' hari' }}</span></div>
@endif
</td>
<td class="px-4 py-3">{{ $d->pembimbing ?: '—' }}<div class="text-[11px] text-slate-500">{{ $d->no_wa_pembimbing }}</div></td>
<td class="px-4 py-3">
@if($d->user)<span class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[10px] font-bold">Punya akun</span>
@else<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Belum ada akun</span>@endif
</td>
<td class="px-4 py-3">@if($zn)<span class="rounded-full bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-bold">{{ $zn['nama'] }}</span>@else<span class="text-slate-400">—</span>@endif</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@if($d->user_id)
@php
  $qrData = [
    'nama' => $d->nama,
    'nis_nim' => $d->nis_nim,
    'departemen' => optional($d->departemen)->nama ?? '-',
    'inisial' => collect(preg_split('/\s+/', $d->nama))->filter()->map(fn ($x) => strtoupper($x[0]))->take(2)->implode(''),
    'qr_url' => route('admin.peserta.qr.image', $d->user_id),
  ];
@endphp
<div class="inline-flex gap-1.5">
<a href="{{ route('admin.peserta.edit', $d->user_id) }}" class="border border-slate-300 hover:bg-slate-50 rounded-lg px-3 py-1.5 font-semibold">Edit</a>
<button type="button" @click="bukaQr(@js($qrData))" class="border border-slate-300 hover:bg-slate-50 rounded-lg px-3 py-1.5 font-semibold">▦ QR</button>
<button type="button" @click="idCard(@js($d->nama))" class="border border-slate-300 hover:bg-slate-50 rounded-lg px-3 py-1.5 font-semibold">🆔 ID Card</button>
</div>
@endif
</td>
</tr>
@empty
<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada peserta aktif.</td></tr>
@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $data->links() }}</div>

{{-- ===== POPUP: QR Kartu ID (dipindah dari halaman Presensi) ===== --}}
<div x-show="modal === 'qr'" x-cloak class="fixed inset-0 z-50 bg-slate-900/40 flex items-center justify-center p-4" @click.self="modal = null">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-xs p-5 text-center relative">
    <button type="button" @click="modal = null" class="absolute right-4 top-4 w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs">✕</button>
    <span class="inline-block bg-blue-50 text-blue-600 text-[10px] font-semibold rounded-full px-3 py-1">Identitas Peserta Magang</span>
    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 font-bold text-sm flex items-center justify-center mx-auto mt-4" x-text="r.inisial"></div>
    <h3 class="font-bold mt-2" x-text="r.nama"></h3>
    <p class="text-xs text-slate-500" x-text="r.departemen"></p>
    <p class="text-[11px] text-slate-400">NIS/NIM: <span x-text="r.nis_nim"></span></p>
    <div class="bg-slate-50 rounded-xl p-3 mt-3 w-full h-[200px] flex items-center justify-center">
      <img x-show="qrSrc" :src="qrSrc" alt="QR" class="w-44 h-44 mx-auto">
      <span x-show="qrLoading" class="text-[11px] text-slate-400">Memuat QR…</span>
      <span x-show="qrError" class="text-[11px] text-red-500">QR gagal dimuat. Tutup lalu buka lagi.</span>
    </div>
    <p class="text-[10px] text-slate-400 mt-2">✔ Digital Verified Badge</p>
    <p class="text-[10px] text-slate-400">Scan QR ini di halaman presensi saat masuk dan pulang.</p>
    <button type="button" @click="downloadPng()" x-show="qrSrc" class="block w-full mt-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg py-2.5">⇩ Download QR (PNG)</button>
  </div>
</div>

{{-- Pemberitahuan singkat (tombol ID Card belum punya rute cetak) --}}
<div x-show="toast" x-cloak x-transition.opacity class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white text-xs rounded-lg px-4 py-2.5 shadow-lg" x-text="toast"></div>

</div>

<script>
  function pesertaAktif() {
    return {
      modal: null, r: {}, qrSrc: '', qrLoading: false, qrError: false, toast: '',
      // Ambil SVG lewat fetch lalu buang karakter liar di depan "<svg" / di belakang "</svg>"
      // (hosting gratis seperti InfinityFree kadang menyisipkan teks/spasi di respons), lalu jadikan data URI.
      async loadQr(url) {
        this.qrSrc = ''; this.qrError = false; this.qrLoading = true;
        try {
          const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
          const txt = await res.text();
          const a = txt.indexOf('<svg'), b = txt.lastIndexOf('</svg>');
          if (!res.ok || a === -1 || b === -1) throw new Error('bukan SVG');
          this.qrSrc = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(txt.slice(a, b + 6));
        } catch (e) { this.qrError = true; }
        this.qrLoading = false;
      },
      bukaQr(row) { this.r = row; this.modal = 'qr'; if (row.qr_url) this.loadQr(row.qr_url); },
      // Ubah SVG jadi PNG 600x600 di browser lewat canvas, jadi tidak butuh ekstensi GD di server.
      downloadPng() {
        if (!this.qrSrc) return;
        const img = new Image();
        img.onload = () => {
          const size = 600, c = document.createElement('canvas');
          c.width = size; c.height = size;
          const ctx = c.getContext('2d');
          ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, size, size);
          ctx.imageSmoothingEnabled = false;
          ctx.drawImage(img, 0, 0, size, size);
          c.toBlob((blob) => {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'QR-' + (this.r.nis_nim ?? 'peserta') + '.png';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 1000);
          }, 'image/png');
        };
        img.src = this.qrSrc;
      },
      // Tombol ID Card: rute cetak belum dibuat, jadi sementara hanya memberi tahu.
      idCard(nama) {
        this.toast = 'Cetak ID Card untuk ' + nama + ' belum tersedia (rutenya menyusul).';
        clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 3000);
      },
    };
  }
</script>
@endsection
