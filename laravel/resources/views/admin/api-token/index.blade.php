@extends('layouts.admin')
@section('title','API Token')
@section('content')
@php
$inp='w-full border border-slate-200 rounded-lg px-3 py-2.5 text-xs bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-200';
$lbl='block text-[11px] font-semibold mb-1';
@endphp

<h1 class="text-2xl font-extrabold">API Token</h1>
<p class="text-xs text-slate-500 mt-1">Akses data prakerin (JSON) untuk divisi IT. Satu token per orang/sistem, bisa dicabut kapan saja.</p>

@if($errors->any())
<div class="mt-4 rounded-lg bg-red-50 text-red-700 text-xs px-4 py-2"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

@if(session('token_baru'))
<div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4" x-data="{salin:false}">
<p class="text-xs font-bold text-amber-800">Token untuk "{{ session('token_baru')['nama'] }}" berhasil dibuat.</p>
<p class="text-[11px] text-amber-700 mt-1">Salin SEKARANG. Token ini hanya tampil sekali dan tidak bisa dilihat lagi setelah halaman ditutup.</p>
<div class="flex gap-2 mt-2">
<input x-ref="tk" readonly value="{{ session('token_baru')['token'] }}" class="{{ $inp }} font-mono" onfocus="this.select()">
<button type="button" @click="$refs.tk.select(); navigator.clipboard && navigator.clipboard.writeText($refs.tk.value); salin=true" class="text-xs font-semibold border border-amber-400 rounded-lg px-4 bg-white whitespace-nowrap" x-text="salin ? 'Tersalin' : 'Salin'"></button>
</div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6 mt-6">
<div class="lg:col-span-1">
<form method="POST" action="{{ route('admin.api-token.store') }}" class="bg-white border border-slate-200 rounded-2xl shadow p-5">
@csrf
<h4 class="text-[10px] font-bold tracking-widest text-blue-600 mb-3">● BUAT TOKEN BARU</h4>
<div class="mb-3"><label class="{{ $lbl }}">Pemilik token *</label><input name="nama" value="{{ old('nama') }}" class="{{ $inp }}" placeholder="Contoh: Budi - Divisi IT" required></div>
<div class="mb-4"><label class="{{ $lbl }}">Berlaku (hari)</label><input type="number" name="berlaku_hari" value="{{ old('berlaku_hari') }}" min="1" max="365" class="{{ $inp }}" placeholder="Kosong = tidak kedaluwarsa"></div>
<button class="w-full bg-blue-600 text-white text-xs font-semibold rounded-lg py-2.5">Buat Token</button>
</form>
</div>

<div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr>
<th class="px-4 py-3">Pemilik</th><th class="px-4 py-3">Awal token</th><th class="px-4 py-3">Berlaku sampai</th><th class="px-4 py-3">Terakhir dipakai</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th>
</tr></thead>
<tbody>
@forelse($tokens as $t)
<tr class="border-t border-slate-100">
<td class="px-4 py-3 font-semibold">{{ $t->nama }}</td>
<td class="px-4 py-3 font-mono">{{ $t->token_awal }}…</td>
<td class="px-4 py-3">{{ $t->berlaku_sampai ? $t->berlaku_sampai->format('d M Y') : 'Tanpa batas' }}</td>
<td class="px-4 py-3">{{ $t->terakhir_dipakai ? $t->terakhir_dipakai->format('d M Y H:i') : 'Belum pernah' }}</td>
<td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $t->statusTeks()==='Aktif' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $t->statusTeks() }}</span></td>
<td class="px-4 py-3 text-right">
@if($t->aktif())
<form method="POST" action="{{ route('admin.api-token.cabut', $t) }}" onsubmit="return confirm('Cabut token ini? Aplikasi yang memakainya akan langsung ditolak.')">@csrf
<button class="text-red-600 font-semibold">Cabut</button></form>
@endif
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada token.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- ===== Uji API: jalankan endpoint dari browser, lihat & unduh hasil JSON ===== --}}
<div class="mt-6 bg-white border border-slate-200 rounded-2xl shadow p-5" x-data="apiExplorer()">
<h4 class="text-[10px] font-bold tracking-widest text-blue-600 mb-1">● UJI API (OUTPUT JSON)</h4>
<p class="text-[11px] text-slate-500 mb-3">Tempel token, pilih endpoint, klik Jalankan. Hasil JSON bisa disalin atau diunduh sebagai file. Token hanya dipakai di halaman ini dan tidak disimpan.</p>
<div class="grid md:grid-cols-3 gap-3">
<div>
<label class="{{ $lbl }}">Contoh endpoint</label>
<select class="{{ $inp }}" @change="path = $event.target.value">
<option value="/ping">/ping (cek token)</option>
<option value="/departemen">/departemen</option>
<option value="/hari-libur?tahun={{ today()->year }}">/hari-libur?tahun=...</option>
<option value="/sekolah">/sekolah</option>
<option value="/presensi?tanggal={{ today()->toDateString() }}">/presensi?tanggal=...</option>
<option value="/rekap?bulan={{ today()->format('Y-m') }}">/rekap?bulan=...</option>
<option value="/peserta?per_page=50">/peserta</option>
<option value="/peserta/NIM_PESERTA">/peserta/{nis_nim}</option>
<option value="/peserta/NIM_PESERTA/presensi?bulan={{ today()->format('Y-m') }}">/peserta/{nis_nim}/presensi</option>
<option value="/peserta/NIM_PESERTA/laporan?bulan={{ today()->format('Y-m') }}">/peserta/{nis_nim}/laporan</option>
<option value="/peserta/NIM_PESERTA/tugas">/peserta/{nis_nim}/tugas</option>
</select>
</div>
<div class="md:col-span-2">
<label class="{{ $lbl }}">Alamat (ganti NIM_PESERTA dengan NIS/NIM asli)</label>
<div class="flex items-center gap-2"><span class="text-[11px] text-slate-400 whitespace-nowrap">/api/v1</span><input x-model="path" class="{{ $inp }} font-mono" spellcheck="false"></div>
</div>
</div>
<div class="flex flex-wrap gap-2 mt-3 items-end">
<div class="flex-1 min-w-[260px]"><label class="{{ $lbl }}">Token (pk_...)</label><input type="password" x-model="token" autocomplete="off" class="{{ $inp }} font-mono" placeholder="Tempel token di sini"></div>
<button type="button" @click="jalankan()" :disabled="loading || !token" class="bg-blue-600 disabled:opacity-40 text-white text-xs font-semibold rounded-lg px-5 py-2.5" x-text="loading ? 'Memuat…' : '▶ Jalankan'"></button>
<button type="button" @click="salin()" :disabled="!hasil" class="border border-slate-300 disabled:opacity-40 text-xs font-semibold rounded-lg px-4 py-2.5" x-text="tersalin ? 'Tersalin' : 'Salin JSON'"></button>
<button type="button" @click="unduh()" :disabled="!hasil" class="border border-slate-300 disabled:opacity-40 text-xs font-semibold rounded-lg px-4 py-2.5">⇩ Unduh .json</button>
</div>
<div class="mt-3 flex items-center gap-2 text-[11px]" x-show="status" x-cloak>
<span class="text-slate-500">Status:</span><span class="font-mono font-semibold" :class="status.startsWith('2') ? 'text-green-600' : 'text-red-600'" x-text="status"></span>
</div>
<pre x-show="hasil" x-cloak class="mt-2 bg-slate-900 text-green-300 text-[11px] leading-relaxed rounded-xl p-4 max-h-[420px] overflow-auto whitespace-pre-wrap break-words" x-text="hasil"></pre>

<details class="mt-4 text-[11px] text-slate-600">
<summary class="cursor-pointer font-semibold">Cara memanggil dari sistem lain (curl)</summary>
<pre class="mt-2 bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto">curl -H "Authorization: Bearer pk_TOKEN_ANDA" \
     -H "Accept: application/json" \
     "{{ url('/api/v1/peserta') }}?per_page=50"</pre>
</details>
</div>

<script>
function apiExplorer(){return{
  path:'/ping', token:'', status:'', hasil:'', loading:false, tersalin:false,
  async jalankan(){
    this.loading=true; this.hasil=''; this.status=''; this.tersalin=false;
    try{
      const r=await fetch(@json(url('/api/v1'))+this.path,{headers:{'Accept':'application/json','Authorization':'Bearer '+this.token.trim()}});
      this.status=r.status+' '+r.statusText;
      const t=await r.text();
      // Buang karakter liar di luar kurung JSON (hosting gratis kadang menyisipkan teks/spasi), lalu rapikan.
      const a=t.search(/[\[{]/), b=Math.max(t.lastIndexOf('}'),t.lastIndexOf(']'));
      try{this.hasil=JSON.stringify(JSON.parse(t.slice(a,b+1)),null,2)}catch(e){this.hasil='Respons BUKAN JSON (potongan awal):\n'+t.slice(0,2000)}
    }catch(e){this.hasil='Gagal: '+e.message}
    this.loading=false;
  },
  salin(){
    if(!this.hasil) return;
    navigator.clipboard && navigator.clipboard.writeText(this.hasil);
    this.tersalin=true; setTimeout(()=>this.tersalin=false,1500);
  },
  unduh(){
    if(!this.hasil) return;
    const nama=(this.path.split('?')[0].replace(/^\//,'').replace(/\//g,'-')||'api')+'.json';
    const a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([this.hasil],{type:'application/json'}));
    a.download=nama; document.body.appendChild(a); a.click(); a.remove();
    setTimeout(()=>URL.revokeObjectURL(a.href),1000);
  }
}}
</script>
@endsection
