@extends('layouts.admin')
@section('title','Tugas')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<h1 class="text-2xl font-extrabold">Tugas</h1>
<p class="text-xs text-slate-500 mt-1">Monitoring tugas yang diunggah peserta magang. Membuka atau mengunduh berkas menandainya sebagai sudah dilihat.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
<div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm"><div class="text-[11px] font-bold text-slate-700">Total tugas</div><div class="text-2xl font-extrabold mt-1">{{ $stat['total'] }}</div></div>
<a href="{{ route('admin.tugas.index', ['status'=>'baru']) }}" class="bg-amber-50 border border-amber-200 rounded-xl p-3"><div class="text-[11px] font-bold text-amber-800">Belum dilihat</div><div class="text-2xl font-extrabold mt-1 text-amber-800">{{ $stat['baru'] }}</div></a>
<div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm"><div class="text-[11px] font-bold text-slate-700">Diunggah hari ini</div><div class="text-2xl font-extrabold mt-1">{{ $stat['hari_ini'] }}</div></div>
<div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm"><div class="text-[11px] font-bold text-slate-700">Peserta yang mengunggah</div><div class="text-2xl font-extrabold mt-1">{{ $stat['peserta'] }}</div></div>
</div>

<form method="GET" action="{{ route('admin.tugas.index') }}" class="flex flex-wrap gap-2 mt-5">
<input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM / nama file" class="{{ $inp }}">
<select name="departemen_id" class="{{ $inp }}">
<option value="">Semua departemen</option>
@foreach($departemen as $d)<option value="{{ $d->id }}" @selected(request('departemen_id')==$d->id)>{{ $d->nama }}</option>@endforeach
</select>
<select name="jenis" class="{{ $inp }}">
<option value="">Semua jenis</option>
<option value="gambar" @selected(request('jenis')==='gambar')>Gambar (jpg/png)</option>
<option value="pdf" @selected(request('jenis')==='pdf')>PDF</option>
<option value="dokumen" @selected(request('jenis')==='dokumen')>Dokumen (doc/docx)</option>
</select>
<select name="status" class="{{ $inp }}">
<option value="">Semua status</option>
<option value="baru" @selected(request('status')==='baru')>Belum dilihat</option>
<option value="dilihat" @selected(request('status')==='dilihat')>Sudah dilihat</option>
</select>
<label class="flex items-center gap-1 text-[11px] text-slate-500">Dari <input type="date" name="dari" value="{{ request('dari') }}" class="{{ $inp }}"></label>
<label class="flex items-center gap-1 text-[11px] text-slate-500">Sampai <input type="date" name="sampai" value="{{ request('sampai') }}" class="{{ $inp }}"></label>
<button class="bg-slate-800 text-white text-xs font-semibold rounded-lg px-4">Filter</button>
<a href="{{ route('admin.tugas.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-4">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">File</th><th class="px-4 py-3">Ukuran</th><th class="px-4 py-3">Diunggah</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody>
@forelse($tugas as $t)
@php $bisaLihat = in_array($t->tipe_mime, ['image/jpeg','image/png','application/pdf'], true); @endphp
<tr class="border-t border-slate-100">
<td class="px-4 py-2.5"><div class="font-semibold">{{ optional($t->user)->name ?? '(akun dihapus)' }}</div><div class="text-slate-400">{{ optional($t->user)->nis_nim }}</div></td>
<td class="px-4 py-2.5">{{ optional(optional($t->user)->departemen)->nama ?? '-' }}</td>
<td class="px-4 py-2.5 max-w-[260px] truncate" title="{{ $t->nama_file_asli }}">{{ $t->nama_file_asli }}</td>
<td class="px-4 py-2.5 whitespace-nowrap">{{ $t->ukuran_kb >= 1024 ? number_format($t->ukuran_kb/1024, 1).' MB' : $t->ukuran_kb.' KB' }}</td>
<td class="px-4 py-2.5 whitespace-nowrap">{{ $t->created_at->translatedFormat('d M Y H:i') }}</td>
<td class="px-4 py-2.5">
@if($t->dilihat_admin_at)<span class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[10px] font-bold">Sudah dilihat</span>
@else<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Baru</span>@endif
</td>
<td class="px-4 py-2.5 text-right whitespace-nowrap">
@if($bisaLihat)<a href="{{ route('admin.tugas.unduh', ['tugas'=>$t->id,'lihat'=>1]) }}" target="_blank" rel="noopener" class="border border-slate-300 rounded-md px-3 py-1 font-semibold hover:bg-slate-50">Lihat</a>@endif
<a href="{{ route('admin.tugas.unduh', $t) }}" class="border border-slate-300 rounded-md px-3 py-1 font-semibold hover:bg-slate-50">Unduh</a>
</td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada tugas yang cocok.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $tugas->links() }}</div>
@endsection