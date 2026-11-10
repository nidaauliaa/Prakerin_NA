@extends('layouts.admin')
@section('title','Arsip '.$dm->nama)
@section('content')
@php
  $badge = [
    'hadir' => 'bg-green-100 text-green-700', 'izin' => 'bg-amber-100 text-amber-700',
    'sakit' => 'bg-blue-100 text-blue-700',   'cuti' => 'bg-teal-100 text-teal-700',
    'alpha' => 'bg-red-100 text-red-600',
  ];
  $warnaTeks = fn($p) => $p === null ? 'text-slate-400' : ($p >= 85 ? 'text-green-600' : ($p >= 70 ? 'text-amber-600' : 'text-red-600'));
  $th = 'px-4 py-3'; $td = 'px-4 py-2.5';
@endphp

<a href="{{ route('admin.arsip.index') }}" class="text-xs text-slate-500 hover:text-slate-800">← Kembali ke Arsip</a>

<div class="flex flex-wrap items-start justify-between gap-4 mt-3">
<div>
<h1 class="text-2xl font-extrabold">{{ $dm->nama }}</h1>
<p class="text-xs text-slate-500 mt-1">
NIS/NIM {{ $dm->nis_nim ?: '—' }} · {{ optional($dm->departemen)->nama ?? 'Tanpa departemen' }}@if($dm->divisi) · {{ $dm->divisi }}@endif
</p>
</div>
@if($dm->isAktif())
<span class="rounded-full bg-green-50 text-green-700 px-3 py-1 text-[11px] font-bold">Masih aktif</span>
@else
<span class="rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-[11px] font-bold">Tidak aktif</span>
@endif
</div>

{{-- Data diri & magang --}}
<div class="grid md:grid-cols-2 gap-4 mt-5">
<div class="border border-slate-200 rounded-2xl shadow-sm p-5">
<div class="text-[11px] font-bold text-slate-500 uppercase mb-3">Data magang</div>
<dl class="grid grid-cols-[130px_1fr] gap-y-1.5 text-xs">
<dt class="text-slate-500">Asal sekolah/kampus</dt><dd>{{ $dm->asal_sekolah ?: '—' }}</dd>
<dt class="text-slate-500">Jurusan</dt><dd>{{ $dm->jurusan ?: '—' }}</dd>
<dt class="text-slate-500">Tipe magang</dt><dd class="uppercase">{{ $dm->tipe_magang }}</dd>
<dt class="text-slate-500">Periode</dt><dd>{{ $dm->tgl_mulai?->translatedFormat('d M Y') ?? '—' }} – {{ $dm->tgl_selesai?->translatedFormat('d M Y') ?? '—' }}</dd>
<dt class="text-slate-500">Tempat prakerin</dt><dd>{{ $dm->tempat_prakerin ?: '—' }}</dd>
<dt class="text-slate-500">Status magang</dt><dd>{{ $dm->status_magang }}</dd>
<dt class="text-slate-500">Keterangan</dt><dd>{{ $dm->keterangan ?: '—' }}</dd>
</dl>
</div>
<div class="border border-slate-200 rounded-2xl shadow-sm p-5">
<div class="text-[11px] font-bold text-slate-500 uppercase mb-3">Kontak</div>
<dl class="grid grid-cols-[130px_1fr] gap-y-1.5 text-xs">
<dt class="text-slate-500">Jenis kelamin</dt><dd>{{ $dm->jenis_kelamin === 'L' ? 'Laki-laki' : ($dm->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</dd>
<dt class="text-slate-500">No. WhatsApp</dt><dd>{{ $dm->no_wa ?: '—' }}</dd>
<dt class="text-slate-500">Alamat</dt><dd>{{ $dm->alamat ?: '—' }}</dd>
<dt class="text-slate-500">Pembimbing</dt><dd>{{ $dm->pembimbing ?: '—' }} {{ $dm->no_wa_pembimbing ? '('.$dm->no_wa_pembimbing.')' : '' }}</dd>
<dt class="text-slate-500">Akun login</dt><dd>{{ $user ? 'Ada (status akun: '.$user->status_akun.')' : 'Belum pernah dibuat' }}</dd>
</dl>
</div>
</div>

@if(! $user)
<div class="mt-5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs px-4 py-3">
Peserta ini belum punya akun login, jadi tidak ada presensi, tugas, atau laporan harian yang tersimpan.
</div>
@else

{{-- Ringkasan kehadiran --}}
<div class="grid grid-cols-3 md:grid-cols-7 gap-3 mt-5">
@foreach([['Hadir',$rekap['hadir'],'text-green-600'],['Izin',$rekap['izin'],'text-amber-600'],['Sakit',$rekap['sakit'],'text-blue-600'],['Cuti',$rekap['cuti'],'text-teal-600'],['Alpha',$rekap['alpha'],'text-red-600'],['Tanpa data',$rekap['tanpa_data'],'text-slate-500']] as [$l,$v,$c])
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold {{ $c }}">{{ $v }}</div><div class="text-[11px] text-slate-500">{{ $l }}</div></div>
@endforeach
<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm"><div class="text-2xl font-bold {{ $warnaTeks($rekap['persen']) }}">{{ $rekap['persen'] === null ? '—' : $rekap['persen'].'%' }}</div><div class="text-[11px] text-slate-500">Kehadiran ({{ $rekap['hari_kerja'] }} hari kerja)</div></div>
</div>
<p class="text-[10px] text-slate-400 mt-2">Persentase = hari hadir ÷ hari kerja (Senin–Jumat) selama masa magang.</p>

<div x-data="{ tab: 'presensi' }" class="mt-6">
<div class="flex gap-6 border-b border-slate-200 text-xs font-semibold">
<button type="button" @click="tab='presensi'" :class="tab==='presensi' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500'" class="pb-2">Presensi ({{ $sesi->count() }})</button>
<button type="button" @click="tab='tugas'" :class="tab==='tugas' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500'" class="pb-2">Tugas ({{ $tugas->count() }})</button>
<button type="button" @click="tab='laporan'" :class="tab==='laporan' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500'" class="pb-2">Laporan Harian ({{ $laporan->count() }})</button>
</div>

{{-- Presensi --}}
<div x-show="tab==='presensi'" class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Sesi</th><th class="{{ $th }}">Waktu</th><th class="{{ $th }}">Lokasi</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Persetujuan</th><th class="{{ $th }}">Foto</th></tr></thead>
<tbody>
@forelse($sesi as $s)
<tr class="border-t border-slate-100">
<td class="{{ $td }} whitespace-nowrap">{{ $s->tanggal->translatedFormat('D, d M Y') }}</td>
<td class="{{ $td }} capitalize">{{ $s->sesi }}</td>
<td class="{{ $td }}">{{ $s->waktu_absen?->format('H:i') ?? '—' }}</td>
<td class="{{ $td }}">{{ $s->lokasi ?: '—' }}@if($s->jarak_meter)<span class="text-slate-400"> · {{ round($s->jarak_meter) }} m</span>@endif</td>
<td class="{{ $td }}"><span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $badge[$s->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($s->status) }}</span></td>
<td class="{{ $td }}">{{ $s->status_persetujuan }}</td>
<td class="{{ $td }}">@if($s->foto_url)<a href="{{ route('admin.arsip.foto', $s) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>@else<span class="text-slate-400">—</span>@endif</td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada data presensi.</td></tr>
@endforelse
</tbody>
</table>
</div>

{{-- Tugas --}}
<div x-show="tab==='tugas'" x-cloak class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="{{ $th }}">Berkas</th><th class="{{ $th }}">Diunggah</th><th class="{{ $th }}">Ukuran</th><th class="{{ $th }}"></th></tr></thead>
<tbody>
@forelse($tugas as $t)
<tr class="border-t border-slate-100">
<td class="{{ $td }} font-semibold">{{ $t->nama_file_asli }}</td>
<td class="{{ $td }} whitespace-nowrap">{{ $t->created_at?->translatedFormat('d M Y H:i') }}</td>
<td class="{{ $td }}">{{ $t->ukuran_kb }} KB</td>
<td class="{{ $td }} text-right"><a href="{{ route('admin.arsip.tugas', $t) }}" class="border border-slate-300 hover:bg-slate-50 rounded-lg px-3 py-1.5 font-semibold">Unduh</a></td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada tugas.</td></tr>
@endforelse
</tbody>
</table>
</div>

{{-- Laporan harian --}}
<div x-show="tab==='laporan'" x-cloak class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Laporan</th><th class="{{ $th }}">Review</th></tr></thead>
<tbody>
@forelse($laporan as $s)
<tr class="border-t border-slate-100 align-top">
<td class="{{ $td }} whitespace-nowrap">{{ $s->tanggal->translatedFormat('D, d M Y') }}</td>
<td class="{{ $td }} whitespace-pre-line">{{ $s->laporanHarian->laporan }}</td>
<td class="{{ $td }}">{{ $s->laporanHarian->status_review }}</td>
</tr>
@empty
<tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada laporan harian.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endif
@endsection
