@extends('layouts.admin')
@section('title','Edit Zona')
@section('content')
@php $inp='w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-200'; @endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
#petaZona{height:460px;border-radius:16px;border:1px solid #e2e8f0;z-index:0}
.leaflet-container{font-family:Inter,sans-serif}
.lbl-zona{background:rgba(15,23,42,.85);color:#fff;border:0;border-radius:6px;font-weight:700;font-size:11px;padding:2px 7px;box-shadow:none}
.lbl-zona:before{display:none}
</style>

<div class="flex flex-wrap items-center justify-between gap-2">
<div>
<h1 class="text-2xl font-extrabold">Edit Zona</h1>
<p class="text-xs text-slate-500 mt-1">Geser peta seperti biasa. <b>Klik dua kali</b> di peta untuk mengisi koordinat ke form di bawah. Klik titik zona yang ada untuk mengeditnya.</p>
</div>
<a href="{{ route('admin.zona.index') }}" class="text-xs font-semibold border border-slate-300 rounded-md px-4 py-1.5 hover:bg-slate-50">← Kembali ke Zona Absen</a>
</div>

@if($errors->any())
<div class="mt-4 rounded-lg bg-red-50 text-red-700 text-xs px-4 py-2"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div id="petaZona" class="mt-4"></div>

<div id="formArea" class="mt-4 bg-white border border-slate-200 rounded-2xl shadow p-5">
<div class="flex flex-wrap items-center justify-between gap-2">
<h2 id="judulForm" class="text-sm font-extrabold">Tambah titik lokasi baru</h2>
<div class="flex gap-2">
<button type="button" onclick="pakaiLokasiSaya()" class="text-[11px] font-semibold border border-slate-300 rounded-md px-3 py-1 hover:bg-slate-50">📍 Pakai lokasi saya</button>
<button type="button" id="btnBatal" onclick="mulaiTambah()" class="hidden text-[11px] font-semibold border border-slate-300 rounded-md px-3 py-1 hover:bg-slate-50">Batal edit</button>
</div>
</div>

<form id="formTitik" method="POST" action="{{ route('admin.zona.titik.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
@csrf
<input type="hidden" name="_method" id="fMethod" value="POST" disabled>
<input type="hidden" id="fId" value="{{ old('zona_id') }}" name="zona_id">
<label class="text-[11px] font-semibold text-slate-600">Nama titik lokasi
<input name="nama" id="fNama" value="{{ old('nama') }}" maxlength="60" placeholder="Contoh: Pos 6 / Gudang" class="{{ $inp }} mt-1" required></label>
<label class="text-[11px] font-semibold text-slate-600">Latitude
<input name="lat" id="fLat" value="{{ old('lat') }}" inputmode="decimal" placeholder="-7.5051948" class="{{ $inp }} mt-1" required></label>
<label class="text-[11px] font-semibold text-slate-600">Longitude
<input name="lng" id="fLng" value="{{ old('lng') }}" inputmode="decimal" placeholder="110.2254398" class="{{ $inp }} mt-1" required></label>
<label class="text-[11px] font-semibold text-slate-600">Ukuran area zona (meter)
<input name="radius" id="fRadius" value="{{ old('radius', 30) }}" inputmode="numeric" placeholder="30" class="{{ $inp }} mt-1" required></label>
<div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-2">
<button id="btnSimpan" class="bg-blue-600 text-white text-xs font-semibold rounded-lg px-5 py-2">Tambah titik lokasi</button>
<button type="button" id="btnHapus" onclick="hapusTitik()" class="hidden border border-red-300 text-red-700 text-xs font-semibold rounded-lg px-4 py-2 hover:bg-red-50">Hapus titik ini</button>
<span class="text-[11px] text-slate-400">Ukuran area = radius lingkaran tempat peserta boleh absen. Perubahan baru berlaku setelah disimpan.</span>
</div>
</form>

<form id="formHapus" method="POST" action="#" class="hidden">@csrf @method('DELETE')</form>
</div>

<div class="mt-4 bg-white border border-slate-200 rounded-2xl shadow overflow-x-auto">
<table class="w-full text-xs">
<thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Latitude</th><th class="px-4 py-3">Longitude</th><th class="px-4 py-3">Area</th><th class="px-4 py-3">Peserta</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
<tbody>
@forelse($zona as $z)
<tr class="border-t border-slate-100">
<td class="px-4 py-2.5 font-semibold">{{ $z['nama'] }}</td>
<td class="px-4 py-2.5 font-mono">{{ $z['lat'] }}</td>
<td class="px-4 py-2.5 font-mono">{{ $z['lng'] }}</td>
<td class="px-4 py-2.5">{{ $z['radius'] }} m</td>
<td class="px-4 py-2.5">{{ $z['peserta'] }}</td>
<td class="px-4 py-2.5 text-right"><button type="button" onclick="pilihZona({{ $z['id'] }}, true)" class="border border-slate-300 rounded-md px-3 py-1 font-semibold hover:bg-slate-50">Edit</button></td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada titik lokasi. Klik dua kali di peta untuk menambah.</td></tr>
@endforelse
</tbody></table>
</div>

<script>
const ZONA = @json($zona);
const URL_TAMBAH = @json(route('admin.zona.titik.store'));
const URL_UBAH = @json(route('admin.zona.titik.update', ['zona' => '__ID__']));
const URL_HAPUS = @json(route('admin.zona.titik.destroy', ['zona' => '__ID__']));
const el = id => document.getElementById(id);

const peta = L.map('petaZona', { doubleClickZoom: false, maxZoom: 20 });
const citra = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
  maxZoom: 20, maxNativeZoom: 19, attribution: 'Tiles &copy; Esri &mdash; Maxar, Earthstar Geographics, GIS User Community'
}).addTo(peta);
const label = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20, maxNativeZoom: 19 });
L.control.layers(null, { 'Label jalan & tempat': label }, { collapsed: true }).addTo(peta);

const lapisZona = {}; // id -> { lingkaran, titik }
let markerBaru = null, lingkaranBaru = null, idEdit = null;

function gambarZona() {
  ZONA.forEach(z => {
    const lingkaran = L.circle([z.lat, z.lng], { radius: z.radius, color: '#22c55e', weight: 2, fillColor: '#22c55e', fillOpacity: .18, interactive: false }).addTo(peta);
    const titik = L.circleMarker([z.lat, z.lng], { radius: 7, color: '#fff', weight: 2, fillColor: '#f59e0b', fillOpacity: 1 }).addTo(peta);
    titik.bindTooltip(z.nama + ' · ' + z.radius + ' m', { permanent: true, direction: 'top', offset: [0, -8], className: 'lbl-zona', interactive: false });
    titik.on('click', () => pilihZona(z.id, false));
    lapisZona[z.id] = { lingkaran, titik };
  });
}

function aturPandangan() {
  if (ZONA.length) {
    const b = L.featureGroup(Object.values(lapisZona).map(l => l.lingkaran)).getBounds();
    peta.fitBounds(b.pad(0.25));
  } else {
    peta.setView([-7.5043105, 110.2266], 17);
  }
}

function angka(v) { const n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? null : n; }

/* Penanda sementara (titik yang sedang diisi di form) */
function taruhPenanda(lat, lng) {
  const r = angka(el('fRadius').value) || 30;
  if (!markerBaru) {
    markerBaru = L.marker([lat, lng], { draggable: true }).addTo(peta);
    markerBaru.on('drag', e => { const p = e.target.getLatLng(); isiKoordinat(p.lat, p.lng, false); });
    lingkaranBaru = L.circle([lat, lng], { radius: r, color: '#2563eb', weight: 2, dashArray: '6 4', fillColor: '#3b82f6', fillOpacity: .15, interactive: false }).addTo(peta);
  } else {
    markerBaru.setLatLng([lat, lng]);
    lingkaranBaru.setLatLng([lat, lng]).setRadius(r);
  }
}
function hapusPenanda() {
  if (markerBaru) { peta.removeLayer(markerBaru); peta.removeLayer(lingkaranBaru); markerBaru = lingkaranBaru = null; }
}
function isiKoordinat(lat, lng, geserPenanda = true) {
  el('fLat').value = lat.toFixed(7);
  el('fLng').value = lng.toFixed(7);
  if (geserPenanda) taruhPenanda(lat, lng);
  else if (lingkaranBaru) lingkaranBaru.setLatLng([lat, lng]);
}

/* Klik dua kali di peta -> isi latitude & longitude di form */
peta.on('dblclick', e => {
  isiKoordinat(e.latlng.lat, e.latlng.lng);
  el('fNama').focus({ preventScroll: true });
});

/* Ubah radius di form -> lingkaran sementara ikut berubah */
el('fRadius').addEventListener('input', () => {
  const r = angka(el('fRadius').value);
  if (lingkaranBaru && r > 0) lingkaranBaru.setRadius(r);
});
['fLat', 'fLng'].forEach(id => el(id).addEventListener('change', () => {
  const la = angka(el('fLat').value), lo = angka(el('fLng').value);
  if (la !== null && lo !== null && Math.abs(la) <= 90 && Math.abs(lo) <= 180) { taruhPenanda(la, lo); peta.panTo([la, lo]); }
}));

function setMode(edit, z) {
  idEdit = edit ? z.id : null;
  el('fId').value = idEdit || '';
  el('judulForm').textContent = edit ? 'Edit titik lokasi: ' + z.nama : 'Tambah titik lokasi baru';
  el('btnSimpan').textContent = edit ? 'Simpan perubahan' : 'Tambah titik lokasi';
  el('btnBatal').classList.toggle('hidden', !edit);
  el('btnHapus').classList.toggle('hidden', !edit);
  el('formTitik').action = edit ? URL_UBAH.replace('__ID__', z.id) : URL_TAMBAH;
  el('fMethod').value = edit ? 'PUT' : 'POST';
  el('fMethod').disabled = !edit;
}

function mulaiTambah() {
  setMode(false);
  ['fNama', 'fLat', 'fLng'].forEach(id => el(id).value = '');
  el('fRadius').value = 30;
  hapusPenanda();
}

function pilihZona(id, geser) {
  const z = ZONA.find(x => x.id === id);
  if (!z) return;
  setMode(true, z);
  el('fNama').value = z.nama;
  el('fRadius').value = z.radius;
  isiKoordinat(z.lat, z.lng);
  if (geser) peta.setView([z.lat, z.lng], Math.max(peta.getZoom(), 18));
  el('formArea').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hapusTitik() {
  const z = ZONA.find(x => x.id === idEdit);
  if (!z) return;
  if (!confirm('Hapus titik lokasi "' + z.nama + '"? Zona yang masih dipakai peserta tidak bisa dihapus.')) return;
  el('formHapus').action = URL_HAPUS.replace('__ID__', z.id);
  el('formHapus').submit();
}

function pakaiLokasiSaya() {
  if (!navigator.geolocation) { alert('Peramban ini tidak mendukung GPS.'); return; }
  navigator.geolocation.getCurrentPosition(
    p => { isiKoordinat(p.coords.latitude, p.coords.longitude); peta.setView([p.coords.latitude, p.coords.longitude], 19); },
    () => alert('Lokasi tidak bisa diambil. Izinkan akses lokasi di peramban, atau klik dua kali di peta.'),
    { enableHighAccuracy: true, timeout: 10000 }
  );
}

gambarZona();
aturPandangan();

/* Setelah gagal validasi: kembalikan mode & penanda dari isian sebelumnya */
(function pulihkan() {
  const idLama = parseInt(el('fId').value, 10);
  const la = angka(el('fLat').value), lo = angka(el('fLng').value);
  if (idLama) {
    const z = ZONA.find(x => x.id === idLama);
    if (z) setMode(true, z);
  }
  if (la !== null && lo !== null) { taruhPenanda(la, lo); peta.setView([la, lo], 18); }
})();
</script>
@endsection