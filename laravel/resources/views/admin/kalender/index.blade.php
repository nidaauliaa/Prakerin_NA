@extends('layouts.admin')
@section('title','Kalender Kerja')
@section('content')
@php
  $inp = 'w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200';
  $lbl = 'block text-[11px] font-semibold text-slate-600 mb-1';
  $awal = [
    'id' => old('_edit_id') ?: null,
    'jenis' => old('jenis', 'cuti_bersama'),
    'jenis_label' => old('jenis_label', ''),
    'nama' => old('nama', ''),
    'mulai' => old('tanggal_mulai', ''),
    'selesai' => old('tanggal_selesai', ''),
    'keterangan' => old('keterangan', ''),
    'ada_pengganti' => (bool) old('ada_pengganti', false),
    'pengganti' => array_values((array) old('pengganti', [])) ?: [''],
  ];
  $hariIni = today();
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
<div>
<h1 class="text-2xl font-extrabold">Kalender Kerja</h1>
<p class="text-xs text-slate-500 mt-1 max-w-3xl">Atur hari libur (cuti bersama, libur nasional, atau libur lain) dan hari kerja pengganti. Tanggal yang diset libur: presensi ditutup, <b>tidak dihitung sebagai hari kerja</b> (persentase kehadiran peserta tidak berkurang) dan tidak menjadi alpha. Hari pengganti (mis. Sabtu) menjadi hari kerja biasa.</p>
</div>
</div>

@if($belumSiap)
<div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs px-4 py-3">Tabel kalender kerja belum dibuat. Jalankan file <b>langkah-fitur-20-okt.sql</b> di phpMyAdmin, lalu muat ulang halaman ini.</div>
@else

@if($errors->any())
<div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div x-data="kalender(@js($awal), @js($payload), {{ $errors->any() ? 'true' : 'false' }})" id="kalenderApp">

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold">{{ $ringkas['jumlah'] }}</div><div class="text-[11px] text-slate-500">Entri libur {{ $tahun }}</div></div>
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold text-red-600">{{ $ringkas['total_hari'] }}</div><div class="text-[11px] text-slate-500">Total hari libur</div></div>
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold text-blue-600">{{ $ringkas['akan_datang'] }}</div><div class="text-[11px] text-slate-500">Akan datang</div></div>
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold text-indigo-600">{{ $ringkas['pengganti'] }}</div><div class="text-[11px] text-slate-500">Hari kerja pengganti</div></div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-6">
<form method="GET" action="{{ route('admin.kalender.index') }}" class="flex items-center gap-2">
<label class="text-[11px] font-semibold text-slate-600">Tahun</label>
<select name="tahun" onchange="this.form.submit()" class="{{ $inp }} !w-auto">
@foreach($tahunTersedia as $t)<option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>@endforeach
</select>
</form>
<button type="button" @click="tambah()" class="bg-blue-600 text-white text-xs font-semibold rounded-lg px-4 py-2">+ Tambah hari libur</button>
</div>

{{-- ===== Form tambah / edit ===== --}}
<div x-show="formBuka" x-cloak id="formKalender" class="mt-4 bg-white border border-slate-200 rounded-2xl shadow p-5">
<div class="flex items-center justify-between">
<h2 class="text-sm font-extrabold" x-text="f.id ? 'Edit hari libur' : 'Tambah hari libur'"></h2>
<button type="button" @click="formBuka = false" class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs">✕</button>
</div>

<form method="POST" :action="aksi()" class="mt-4 space-y-4">
@csrf
<input type="hidden" name="_method" value="PUT" :disabled="!f.id">
<input type="hidden" name="_edit_id" :value="f.id ?? ''">

<div class="grid md:grid-cols-3 gap-3">
<div>
<label class="{{ $lbl }}">Jenis libur</label>
<select name="jenis" x-model="f.jenis" class="{{ $inp }}">
@foreach(\App\Models\HariLibur::JENIS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
</select>
</div>
<div x-show="f.jenis === 'lainnya'" x-cloak>
<label class="{{ $lbl }}">Tulis jenis liburnya</label>
<input name="jenis_label" x-model="f.jenis_label" maxlength="60" placeholder="Mis. Libur Perusahaan" class="{{ $inp }}">
</div>
<div :class="f.jenis === 'lainnya' ? '' : 'md:col-span-2'">
<label class="{{ $lbl }}">Nama / keterangan libur</label>
<input name="nama" x-model="f.nama" maxlength="100" placeholder="Mis. Hari Raya Idul Fitri" class="{{ $inp }}" required>
</div>
</div>

<div>
<div class="grid md:grid-cols-3 gap-3">
<div>
<label class="{{ $lbl }}">Tanggal mulai</label>
<input type="date" name="tanggal_mulai" x-model="f.mulai" @change="if (!f.selesai || f.selesai < f.mulai) f.selesai = f.mulai" class="{{ $inp }}" required>
</div>
<div>
<label class="{{ $lbl }}">Tanggal selesai <span class="font-normal text-slate-400">(sama dengan mulai bila 1 hari)</span></label>
<input type="date" name="tanggal_selesai" x-model="f.selesai" :min="f.mulai" class="{{ $inp }}" required>
</div>
<div>
<label class="{{ $lbl }}">Pilih cepat lama libur</label>
<div class="flex flex-wrap gap-1.5">
<template x-for="o in [[1,'1 hari'],[2,'2 hari'],[3,'3 hari'],[7,'1 minggu'],[14,'2 minggu']]" :key="o[0]">
<button type="button" @click="setLama(o[0])" class="border border-slate-300 hover:bg-slate-50 rounded-md px-2.5 py-1.5 text-[11px] font-semibold" x-text="o[1]"></button>
</template>
</div>
</div>
</div>
<p class="mt-2 text-[11px] text-slate-500" x-show="lama() > 0" x-cloak>
Libur <b x-text="hariNama(f.mulai)"></b><span x-show="lama() > 1"> sampai <b x-text="hariNama(f.selesai)"></b></span> · <b x-text="lama() + ' hari'"></b> (termasuk Sabtu/Minggu di dalam rentang).
</p>
</div>

<div>
<label class="{{ $lbl }}">Hari pengganti</label>
<input type="hidden" name="ada_pengganti" :value="f.ada_pengganti ? 1 : 0">
<div class="flex gap-4 text-xs">
<label class="flex items-center gap-1.5"><input type="radio" :checked="!f.ada_pengganti" @change="f.ada_pengganti = false"> Tidak ada hari pengganti</label>
<label class="flex items-center gap-1.5"><input type="radio" :checked="f.ada_pengganti" @change="f.ada_pengganti = true; if (!f.pengganti.length) f.pengganti = ['']"> Ada hari pengganti</label>
</div>
<div x-show="f.ada_pengganti" x-cloak class="mt-2 space-y-2">
<p class="text-[11px] text-slate-500">Tanggal yang biasanya libur (mis. Sabtu) tetapi diganti menjadi <b>hari kerja</b>: peserta wajib presensi dan harinya dihitung di persentase kehadiran.</p>
<template x-for="(p, i) in f.pengganti" :key="i">
<div class="flex items-center gap-2">
<input type="date" name="pengganti[]" x-model="f.pengganti[i]" class="{{ $inp }} !w-48">
<span class="text-[11px] text-slate-500" x-text="hariNama(f.pengganti[i])"></span>
<button type="button" @click="f.pengganti.splice(i, 1); if (!f.pengganti.length) f.pengganti = ['']" class="text-[11px] text-red-500">✕ hapus</button>
</div>
</template>
<button type="button" @click="f.pengganti.push('')" x-show="f.pengganti.length < 10" class="text-[11px] font-semibold text-blue-600">+ Tambah tanggal pengganti</button>
</div>
</div>

<div>
<label class="{{ $lbl }}">Catatan (opsional)</label>
<input name="keterangan" x-model="f.keterangan" maxlength="255" placeholder="Mis. sesuai SKB 3 Menteri / keputusan direksi" class="{{ $inp }}">
</div>

<div class="flex items-center gap-2">
<button class="bg-blue-600 text-white text-xs font-semibold rounded-lg px-5 py-2.5" x-text="f.id ? 'Simpan perubahan' : 'Tambah hari libur'"></button>
<button type="button" @click="formBuka = false" class="border border-slate-300 text-xs font-semibold rounded-lg px-4 py-2.5">Batal</button>
</div>
</form>
</div>

{{-- ===== Daftar ===== --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Lama</th><th class="px-4 py-3">Libur</th><th class="px-4 py-3">Hari pengganti</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody>
@forelse($daftar as $h)
@php
  $m = $h->tanggal_mulai->copy()->locale('id'); $s = $h->tanggal_selesai->copy()->locale('id');
  $badge = ['cuti_bersama' => 'bg-amber-50 text-amber-700', 'libur_nasional' => 'bg-red-50 text-red-600', 'lainnya' => 'bg-slate-100 text-slate-600'][$h->jenis];
@endphp
<tr class="border-t border-slate-100 align-top">
<td class="px-4 py-3 whitespace-nowrap font-semibold">
@if($h->lamaHari() === 1){{ $m->translatedFormat('D, j M Y') }}@else{{ $m->translatedFormat('D, j M') }} – {{ $s->translatedFormat('D, j M Y') }}@endif
</td>
<td class="px-4 py-3 whitespace-nowrap">{{ $h->lamaHari() }} hari</td>
<td class="px-4 py-3">
<div class="font-semibold">{{ $h->nama }}</div>
<span class="inline-block mt-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $badge }}">{{ $h->labelJenis() }}</span>
@if($h->keterangan)<div class="text-[11px] text-slate-400 mt-1">{{ $h->keterangan }}</div>@endif
</td>
<td class="px-4 py-3">
@forelse($h->pengganti as $p)<div class="whitespace-nowrap text-indigo-700 font-semibold">{{ $p->tanggal->copy()->locale('id')->translatedFormat('l, j M Y') }}</div>
@empty<span class="text-slate-400">—</span>@endforelse
</td>
<td class="px-4 py-3">
@if($hariIni->between($h->tanggal_mulai, $h->tanggal_selesai))<span class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[10px] font-bold">Berlangsung</span>
@elseif($h->tanggal_mulai->gt($hariIni))<span class="rounded-full bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-bold">Akan datang</span>
@else<span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[10px] font-bold">Selesai</span>@endif
</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
<button type="button" @click="edit({{ $h->id }})" class="border border-slate-300 rounded-md px-3 py-1 font-semibold hover:bg-slate-50">Edit</button>
<form method="POST" action="{{ route('admin.kalender.destroy', $h) }}" class="inline" onsubmit="return confirm('Hapus hari libur &quot;{{ addslashes($h->nama) }}&quot;? Tanggalnya kembali dihitung normal (termasuk di persentase kehadiran).')">@csrf @method('DELETE')
<button class="border border-red-300 text-red-600 rounded-md px-3 py-1 font-semibold hover:bg-red-50">Hapus</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada hari libur di {{ $tahun }}. Klik "+ Tambah hari libur".</td></tr>
@endforelse
</tbody></table>
</div>
<p class="mt-3 text-[11px] text-slate-400">Mengubah atau menghapus libur yang sudah lewat ikut mengubah persentase kehadiran bulan itu, karena jumlah hari kerjanya berubah.</p>

</div>

<script>
const URL_TAMBAH = @json(route('admin.kalender.store'));
const URL_UBAH = @json(route('admin.kalender.update', ['libur' => '__ID__']));

function kalender(awal, payload, buka) {
  return {
    f: awal, payload: payload, formBuka: buka || !!(awal.nama || awal.mulai),
    aksi() { return this.f.id ? URL_UBAH.replace('__ID__', this.f.id) : URL_TAMBAH; },
    kosong() {
      return { id: null, jenis: 'cuti_bersama', jenis_label: '', nama: '', mulai: '', selesai: '', keterangan: '', ada_pengganti: false, pengganti: [''] };
    },
    tambah() { this.f = this.kosong(); this.formBuka = true; this.gulir(); },
    edit(id) {
      const p = this.payload[id]; if (!p) return;
      this.f = { id: p.id, jenis: p.jenis, jenis_label: p.jenis_label || '', nama: p.nama, mulai: p.mulai, selesai: p.selesai, keterangan: p.keterangan || '',
                 ada_pengganti: p.pengganti.length > 0, pengganti: p.pengganti.length ? p.pengganti.slice() : [''] };
      this.formBuka = true; this.gulir();
    },
    gulir() { this.$nextTick(() => { const e = document.getElementById('formKalender'); if (e) e.scrollIntoView({ behavior: 'smooth', block: 'start' }); }); },
    tambahHari(s, n) { const a = s.split('-').map(Number); return new Date(Date.UTC(a[0], a[1] - 1, a[2] + n)).toISOString().slice(0, 10); },
    setLama(n) { if (!this.f.mulai) { alert('Isi tanggal mulai dulu.'); return; } this.f.selesai = this.tambahHari(this.f.mulai, n - 1); },
    lama() {
      if (!this.f.mulai || !this.f.selesai || this.f.selesai < this.f.mulai) return 0;
      const a = this.f.mulai.split('-').map(Number), b = this.f.selesai.split('-').map(Number);
      return Math.round((Date.UTC(b[0], b[1] - 1, b[2]) - Date.UTC(a[0], a[1] - 1, a[2])) / 86400000) + 1;
    },
    hariNama(s) {
      if (!s) return '';
      return new Date(s + 'T00:00:00Z').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
    },
  };
}
</script>
@endif
@endsection
