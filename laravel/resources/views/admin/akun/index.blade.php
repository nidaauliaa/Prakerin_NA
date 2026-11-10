@extends('layouts.admin')
@section('title','Akun Peserta')
@section('content')
@php $inp='border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<h1 class="text-2xl font-extrabold">Akun Peserta</h1>
<p class="text-xs text-slate-500 mt-1">Reset kata sandi peserta yang lupa. Reset hanya mengembalikan kata sandi ke sandi awal ({{ \App\Models\User::PASSWORD_DEFAULT }}); riwayat absen dan tugas tidak dihapus. Peserta wajib membuat sandi baru saat masuk.</p>

@if($errors->any())
<div class="mt-4 rounded-lg bg-red-50 text-red-700 text-xs px-4 py-2"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

{{-- ===== Permintaan masuk ===== --}}
<div class="mt-6 flex items-center gap-2">
<h2 class="text-sm font-extrabold">Permintaan reset kata sandi</h2>
@if($menunggu->count())<span class="rounded-full bg-red-500 text-white text-[10px] font-bold px-2 py-0.5">{{ $menunggu->count() }} baru</span>@endif
</div>

@forelse($menunggu as $p)
@php
  $u = $p->user;
  $namaCocok = $u && mb_strtolower(trim($u->name)) === mb_strtolower(trim($p->nama));
  $divisiCocok = $u && $u->departemen && mb_strtolower(trim($u->departemen->nama)) === mb_strtolower(trim($p->divisi));
@endphp
<div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
<div class="flex flex-wrap items-start justify-between gap-3">
<div class="text-xs">
<div class="font-extrabold text-sm">{{ $p->nama }} <span class="font-semibold text-slate-500">· {{ $p->nis_nim }}</span></div>
<div class="mt-1 text-slate-600">Divisi diisi: <b>{{ $p->divisi }}</b> · dikirim {{ $p->created_at->translatedFormat('d M Y H:i') }}</div>
<div class="mt-2 flex flex-wrap gap-1.5">
@if($u)
<span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $namaCocok ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $namaCocok ? 'Nama cocok dengan data' : 'Nama BERBEDA dari data: '.$u->name }}</span>
<span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $divisiCocok ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $divisiCocok ? 'Divisi cocok dengan data' : 'Divisi di data: '.(optional($u->departemen)->nama ?? '-') }}</span>
@else
<span class="rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-[10px] font-bold">Akun tidak ditemukan</span>
@endif
</div>
<p class="mt-2 text-slate-700 whitespace-pre-line"><b>Alasan:</b> {{ $p->alasan }}</p>
</div>
<div class="flex flex-col gap-2 w-full sm:w-auto sm:min-w-[210px]">
<form method="POST" action="{{ route('admin.akun.selesaikan', $p) }}" onsubmit="return confirm('Reset sandi {{ addslashes($p->nama) }} ke sandi awal?')">@csrf
<button class="w-full bg-blue-600 text-white text-xs font-semibold rounded-lg px-4 py-2" @disabled(!$u)>Reset kata sandi</button>
</form>
<form method="POST" action="{{ route('admin.akun.tolak', $p) }}" class="flex gap-1">@csrf
<input name="catatan_admin" placeholder="Catatan (opsional)" maxlength="255" class="{{ $inp }} flex-1 min-w-0">
<button class="border border-slate-300 bg-white text-xs font-semibold rounded-lg px-3">Tolak</button>
</form>
</div>
</div>
</div>
@empty
<div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-500 px-4 py-5 text-center">Tidak ada permintaan yang menunggu.</div>
@endforelse

@if($riwayat->count())
<details class="mt-3 text-xs">
<summary class="cursor-pointer font-semibold text-slate-600">Riwayat permintaan terakhir</summary>
<div class="mt-2 bg-white border border-slate-200 rounded-xl overflow-x-auto">
<table class="w-full text-xs"><thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Peserta</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Ditangani</th><th class="px-4 py-2">Catatan</th></tr></thead><tbody>
@foreach($riwayat as $h)
<tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $h->nama }} <span class="text-slate-400">· {{ $h->nis_nim }}</span></td>
<td class="px-4 py-2"><span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $h->status==='selesai' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ $h->status==='selesai' ? 'Sudah direset' : 'Ditolak' }}</span></td>
<td class="px-4 py-2">{{ $h->ditangani_oleh }} · {{ optional($h->ditangani_at)->translatedFormat('d M Y H:i') }}</td>
<td class="px-4 py-2">{{ $h->catatan_admin ?? '-' }}</td></tr>
@endforeach
</tbody></table></div>
</details>
@endif

{{-- ===== Semua akun ===== --}}
<h2 class="text-sm font-extrabold mt-8">Semua akun peserta</h2>
<form method="GET" action="{{ route('admin.akun.index') }}" class="flex flex-wrap gap-2 mt-2">
<input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM" class="{{ $inp }}">
<select name="departemen_id" class="{{ $inp }}">
<option value="">Semua departemen</option>
@foreach($departemen as $d)<option value="{{ $d->id }}" @selected(request('departemen_id')==$d->id)>{{ $d->nama }}</option>@endforeach
</select>
<select name="sandi" class="{{ $inp }}">
<option value="">Semua status sandi</option>
<option value="awal" @selected(request('sandi')==='awal')>Masih sandi awal</option>
<option value="diganti" @selected(request('sandi')==='diganti')>Sudah ganti sandi</option>
</select>
<button class="bg-slate-800 text-white text-xs font-semibold rounded-lg px-4">Filter</button>
<a href="{{ route('admin.akun.index') }}" class="text-xs text-slate-500 self-center">Reset</a>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto mt-3">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">NIM/NISN (username)</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Status sandi</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
<tbody>
@forelse($peserta as $u)
<tr class="border-t border-slate-100">
<td class="px-4 py-2.5 font-semibold">{{ $u->name }}</td>
<td class="px-4 py-2.5">{{ $u->nis_nim }}</td>
<td class="px-4 py-2.5">{{ optional($u->departemen)->nama ?? '-' }}</td>
<td class="px-4 py-2.5">
@if($u->wajib_ganti_password)<span class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[10px] font-bold">Masih sandi awal</span>
@else<span class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[10px] font-bold">Sudah ganti sandi</span>@endif
</td>
<td class="px-4 py-2.5 text-right">
<form method="POST" action="{{ route('admin.akun.reset', $u) }}" onsubmit="return confirm('Reset sandi {{ addslashes($u->name) }} ke sandi awal? Riwayat absen dan tugas tetap ada.')">@csrf
<button class="border border-slate-300 rounded-md px-3 py-1 font-semibold hover:bg-slate-50">Reset sandi</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada akun.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $peserta->links() }}</div>
@endsection