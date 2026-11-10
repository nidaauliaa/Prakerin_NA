@extends('layouts.admin')
@section('title','Arsip Peserta')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<h1 class="text-2xl font-extrabold">Arsip Peserta</h1>
<p class="text-xs text-slate-500 mt-1">Peserta yang sudah tidak aktif (tanggal selesai magang sudah lewat, atau status bukan ACTIVE). Buka arsip untuk melihat presensi, tugas, dan laporan harian selama magang.</p>

<div class="border border-slate-200 rounded-xl px-4 py-3 shadow-sm inline-block mt-5">
<div class="text-2xl font-bold text-slate-700">{{ $total }}</div>
<div class="text-[11px] text-slate-500">Peserta di arsip</div>
</div>

<form method="GET" action="{{ route('admin.arsip.index') }}" class="flex flex-wrap gap-2 mt-5">
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
<a href="{{ route('admin.arsip.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Peserta</th>
<th class="px-4 py-3">Sekolah / Kampus</th>
<th class="px-4 py-3">Departemen</th>
<th class="px-4 py-3">Periode magang</th>
<th class="px-4 py-3">Alasan di arsip</th>
<th class="px-4 py-3">Isi arsip</th>
<th class="px-4 py-3"></th>
</tr></thead>
<tbody>
@forelse($data as $d)
<tr class="border-t border-slate-100 align-top">
<td class="px-4 py-3"><div class="font-semibold">{{ $d->nama }}</div><div class="text-[11px] text-slate-500">{{ $d->nis_nim ?: '—' }}</div></td>
<td class="px-4 py-3">{{ $d->asal_sekolah ?: '—' }}<div class="text-[11px] text-slate-500">{{ $d->jurusan }}</div></td>
<td class="px-4 py-3">{{ optional($d->departemen)->nama ?? '—' }}</td>
<td class="px-4 py-3 whitespace-nowrap">{{ $d->tgl_mulai?->translatedFormat('d M Y') ?? '—' }} – {{ $d->tgl_selesai?->translatedFormat('d M Y') ?? '—' }}</td>
<td class="px-4 py-3">
@if(strtoupper((string) $d->status_magang) !== 'ACTIVE')<span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[10px] font-bold">Status: {{ $d->status_magang }}</span>
@else<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Masa magang selesai</span>@endif
</td>
<td class="px-4 py-3 text-[11px] text-slate-600">
@if($d->user_id)
{{ $hariPresensi[$d->user_id] ?? 0 }} hari presensi · {{ $jumlahTugas[$d->user_id] ?? 0 }} tugas · {{ $jumlahLaporan[$d->user_id] ?? 0 }} laporan
@else<span class="text-slate-400">Belum punya akun login</span>@endif
</td>
<td class="px-4 py-3 text-right whitespace-nowrap"><a href="{{ route('admin.arsip.show', $d) }}" class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-3 py-1.5 font-semibold">Buka Arsip</a></td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada peserta di arsip.</td></tr>
@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $data->links() }}</div>
@endsection
