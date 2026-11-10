@extends('layouts.admin')
@section('title','Lokasi Curiga')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<h1 class="text-2xl font-extrabold">Deteksi Abnormal GPS</h1>
<p class="text-xs text-slate-500 mt-1">Catatan presensi yang terindikasi memakai lokasi palsu (fake GPS). <b>Diblokir</b> = presensi ditolak sistem. <b>Ditahan</b> = presensi tersimpan tetapi menunggu ACC admin. Ini indikasi dari pola data, bukan bukti pasti, jadi tinjau dulu sebelum mengambil tindakan.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
<div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm"><div class="text-[11px] font-bold text-slate-700">Total kejadian</div><div class="text-2xl font-extrabold mt-1">{{ $stat['total'] }}</div></div>
<a href="{{ route('admin.lokasi-curiga.index', array_merge(request()->query(), ['tindakan'=>'diblokir'])) }}" class="bg-red-50 border border-red-200 rounded-xl p-3"><div class="text-[11px] font-bold text-red-800">Diblokir</div><div class="text-2xl font-extrabold mt-1 text-red-700">{{ $stat['diblokir'] }}</div></a>
<a href="{{ route('admin.lokasi-curiga.index', array_merge(request()->query(), ['tindakan'=>'ditahan'])) }}" class="bg-amber-50 border border-amber-200 rounded-xl p-3"><div class="text-[11px] font-bold text-amber-800">Ditahan (menunggu ACC)</div><div class="text-2xl font-extrabold mt-1 text-amber-700">{{ $stat['ditahan'] }}</div></a>
<div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm"><div class="text-[11px] font-bold text-slate-700">Peserta terlibat</div><div class="text-2xl font-extrabold mt-1">{{ $stat['peserta'] }}</div></div>
</div>

@if($teratas->isNotEmpty())
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm mt-4 p-4">
<div class="text-xs font-bold text-slate-700 mb-2">Peserta yang paling sering muncul pada periode ini</div>
<div class="flex flex-wrap gap-2">
@foreach($teratas as $t)
<div class="border border-slate-200 rounded-lg px-3 py-2 text-xs">
<div class="font-semibold">{{ optional($t->user)->name ?? '(akun dihapus)' }}</div>
<div class="text-slate-500">{{ $t->jml_total }} kejadian &middot; <span class="text-red-600 font-semibold">{{ $t->jml_diblokir }} diblokir</span> &middot; <span class="text-amber-600 font-semibold">{{ $t->jml_ditahan }} ditahan</span></div>
</div>
@endforeach
</div>
</div>
@endif

<form method="GET" action="{{ route('admin.lokasi-curiga.index') }}" class="flex flex-wrap gap-2 mt-5">
<input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM" class="{{ $inp }}">
<select name="departemen_id" class="{{ $inp }}">
<option value="">Semua departemen</option>
@foreach($departemen as $d)<option value="{{ $d->id }}" @selected(request('departemen_id')==$d->id)>{{ $d->nama }}</option>@endforeach
</select>
<select name="tindakan" class="{{ $inp }}">
<option value="">Semua tindakan</option>
<option value="diblokir" @selected(request('tindakan')==='diblokir')>Diblokir</option>
<option value="ditahan" @selected(request('tindakan')==='ditahan')>Ditahan</option>
</select>
<label class="flex items-center gap-1 text-[11px] text-slate-500">Dari <input type="date" name="dari" value="{{ $dari }}" class="{{ $inp }}"></label>
<label class="flex items-center gap-1 text-[11px] text-slate-500">Sampai <input type="date" name="sampai" value="{{ $sampai }}" class="{{ $inp }}"></label>
<button class="bg-slate-800 text-white text-xs font-semibold rounded-lg px-4">Filter</button>
<a href="{{ route('admin.lokasi-curiga.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Tindakan</th><th class="px-4 py-3">Alasan terdeteksi</th><th class="px-4 py-3">Lokasi</th><th class="px-4 py-3">Perangkat</th>
</tr></thead>
<tbody>
@forelse($log as $l)
<tr class="border-t border-slate-100 align-top">
<td class="px-4 py-2.5 whitespace-nowrap">{{ $l->created_at->translatedFormat('d M Y') }}@if($l->dilihat_admin_at === null)<span class="ml-1 rounded-full bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 align-middle">BARU</span>@endif<div class="text-slate-400">{{ $l->created_at->format('H:i:s') }}@if($l->sesi) &middot; sesi {{ $l->sesi }}@endif</div></td>
<td class="px-4 py-2.5"><div class="font-semibold">{{ optional($l->user)->name ?? '(akun dihapus)' }}</div><div class="text-slate-400">{{ optional($l->user)->nis_nim }} @if(optional(optional($l->user)->departemen)->nama)&middot; {{ $l->user->departemen->nama }}@endif</div></td>
<td class="px-4 py-2.5 whitespace-nowrap">
@if($l->tindakan==='diblokir')<span class="rounded-full bg-red-50 text-red-700 px-2 py-0.5 text-[10px] font-bold">Diblokir</span>
@else<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Ditahan</span>@endif
<div class="text-slate-400 mt-1">skor {{ $l->skor }}</div>
</td>
<td class="px-4 py-2.5 min-w-[260px]">
<ul class="list-disc pl-4 space-y-0.5">
@foreach(($l->alasan ?? []) as $a)<li>{{ $a }}</li>@endforeach
</ul>
</td>
<td class="px-4 py-2.5 whitespace-nowrap">
@if($l->lat !== null && $l->lng !== null)
<a href="https://www.google.com/maps?q={{ $l->lat }},{{ $l->lng }}" target="_blank" rel="noopener" class="text-blue-600 font-semibold hover:underline">Lihat peta</a>
<div class="text-slate-400">{{ number_format((float)$l->lat,6) }}, {{ number_format((float)$l->lng,6) }}</div>
<div class="text-slate-400">akurasi {{ $l->akurasi !== null ? rtrim(rtrim(number_format((float)$l->akurasi,2),'0'),'.') : '-' }} m</div>
@else - @endif
</td>
<td class="px-4 py-2.5 max-w-[220px]">
<div class="truncate" title="{{ $l->device_id }}">{{ $l->device_id ?? '-' }}</div>
<div class="text-slate-400">{{ $l->ip }}</div>
<div class="text-slate-400 truncate" title="{{ $l->user_agent }}">{{ \Illuminate\Support\Str::limit($l->user_agent, 40) }}</div>
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Tidak ada kejadian lokasi mencurigakan pada periode ini.</td></tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-4">{{ $log->links() }}</div>
@endsection