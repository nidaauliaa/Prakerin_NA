@extends('layouts.admin')
@section('title','Zona Absen')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<h1 class="text-2xl font-extrabold">Zona Absen</h1>
<p class="text-xs text-slate-500 mt-1">Tentukan di pos mana tiap peserta boleh absen. Satu peserta = satu zona. Peserta yang belum punya zona tidak bisa absen.</p>

@if($errors->any())
<div class="mt-4 rounded-lg bg-red-50 text-red-700 text-xs px-4 py-2"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-5">
@foreach($pos as $p)
<a href="{{ route('admin.zona.index', ['zona'=>$p['kode']]) }}" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm hover:border-blue-300">
<div class="text-[11px] font-bold text-slate-700">{{ $p['nama'] }}</div>
<div class="text-2xl font-extrabold mt-1">{{ $ringkasan[$p['kode']] ?? 0 }}</div>
<div class="text-[10px] text-slate-400">radius {{ $p['radius'] }} m</div>
</a>
@endforeach
<a href="{{ route('admin.zona.index', ['zona'=>'kosong']) }}" class="bg-amber-50 border border-amber-200 rounded-xl p-3">
<div class="text-[11px] font-bold text-amber-800">Belum diatur</div>
<div class="text-2xl font-extrabold mt-1 text-amber-800">{{ $ringkasan[''] ?? 0 }}</div>
<div class="text-[10px] text-amber-600">belum bisa absen</div>
</a>
</div>
<div class="flex justify-end mt-3">
<a href="{{ route('admin.zona.edit') }}" class="inline-flex items-center gap-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg px-4 py-2 hover:bg-slate-700">📍 Edit Zona</a>
</div>

<form method="GET" action="{{ route('admin.zona.index') }}" class="flex flex-wrap gap-2 mt-5">
<input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM" class="{{ $inp }}">
<select name="departemen_id" class="{{ $inp }}">
<option value="">Semua departemen</option>
@foreach($departemen as $d)<option value="{{ $d->id }}" @selected(request('departemen_id')==$d->id)>{{ $d->nama }}</option>@endforeach
</select>
<select name="zona" class="{{ $inp }}">
<option value="">Semua zona</option>
<option value="kosong" @selected(request('zona')==='kosong')>Belum diatur</option>
@foreach($pos as $p)<option value="{{ $p['kode'] }}" @selected(request('zona')===$p['kode'])>{{ $p['nama'] }}</option>@endforeach
</select>
<button class="bg-slate-800 text-white text-xs font-semibold rounded-lg px-4">Filter</button>
<a href="{{ route('admin.zona.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<form method="POST" action="{{ route('admin.zona.update') }}" class="mt-4">
@csrf
<div class="flex flex-wrap items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
<span class="text-xs font-semibold text-blue-800">Atur zona untuk peserta yang dicentang:</span>
<select name="pos_kode" class="{{ $inp }}">
<option value="">(Kosongkan zona)</option>
@foreach($pos as $p)<option value="{{ $p['kode'] }}">{{ $p['nama'] }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white text-xs font-semibold rounded-lg px-4 py-2">Terapkan</button>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-3">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3 w-8"><input type="checkbox" onclick="var s=this.checked;document.querySelectorAll('.cb').forEach(function(c){c.checked=s})"></th>
<th class="px-4 py-3">Nama</th><th class="px-4 py-3">NIM/NISN</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Zona saat ini</th>
</tr></thead>
<tbody>
@forelse($peserta as $u)
@php $zn = collect($pos)->firstWhere('kode', $u->pos_kode); @endphp
<tr class="border-t border-slate-100">
<td class="px-4 py-2.5"><input type="checkbox" class="cb" name="user_ids[]" value="{{ $u->id }}"></td>
<td class="px-4 py-2.5 font-semibold">{{ $u->name }}</td>
<td class="px-4 py-2.5">{{ $u->nis_nim }}</td>
<td class="px-4 py-2.5">{{ optional($u->departemen)->nama ?? '-' }}</td>
<td class="px-4 py-2.5">
@if($zn)<span class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[10px] font-bold">{{ $zn['nama'] }}</span>
@elseif($u->pos_kode)<span class="rounded-full bg-red-50 text-red-700 px-2 py-0.5 text-[10px] font-bold">Kode tidak dikenal: {{ $u->pos_kode }}</span>
@else<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Belum diatur</span>@endif
</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada peserta.</td></tr>
@endforelse
</tbody>
</table>
</div>
</form>

<div class="mt-4">{{ $peserta->links() }}</div>
@endsection