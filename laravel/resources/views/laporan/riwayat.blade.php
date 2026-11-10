@extends('layouts.app')
@section('judul', 'Riwayat Laporan Harian')

@push('css')
<style>
.item{background:#fff;border:1px solid var(--bd);border-radius:14px;padding:14px;margin-bottom:12px}
.item .top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.item .tgl{font-weight:700;font-size:13.5px}
.badge{font-size:10.5px;font-weight:700;padding:4px 9px;border-radius:20px}
.badge.disetujui{background:#e7faf1;color:#0f7a3a}
.badge.menunggu{background:#fef3e2;color:#9a5b0a}
.badge.ditolak{background:#fef2f2;color:var(--mr)}
.lbl{font-size:10px;font-weight:700;letter-spacing:.04em;color:#94a3b8;text-transform:uppercase;margin-bottom:3px}
.txt{font-size:13px;color:var(--nv);line-height:1.5;margin-bottom:10px;white-space:pre-wrap}
.kosong{text-align:center;color:var(--gy);font-size:13px;padding:30px 0}
.pager{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:14px 0;font-size:12.5px;color:var(--gy)}
.pager a,.pager span.off{border:1px solid var(--bd);border-radius:10px;padding:8px 14px;font-weight:600;text-decoration:none;background:#fff;color:var(--nv)}
.pager span.off{opacity:.4}
</style>
@endpush

@section('konten')
<h1>Riwayat Laporan Harian</h1>
<p class="sub">Daftar laporan yang sudah kamu kirim, beserta status review mentor.</p>

@forelse ($laporan as $l)
  <div class="item">
    <div class="top">
      <div class="tgl">{{ $l->sesiPresensi->tanggal->translatedFormat('d F Y') }}</div>
      <span class="badge {{ $l->status_review }}">
        {{ match($l->status_review) { 'disetujui' => '✓ Disetujui', 'ditolak' => '✕ Ditolak', default => '⏳ Menunggu' } }}
      </span>
    </div>
    <div class="lbl">Laporan Harian</div>
    <div class="txt">{{ $l->laporan }}</div>
  </div>
@empty
  <div class="kosong">Belum ada laporan harian yang dikirim.</div>
@endforelse

@if ($laporan->hasPages())
  <div class="pager">
    @if ($laporan->onFirstPage()) <span class="off">‹ Sebelumnya</span> @else <a href="{{ $laporan->previousPageUrl() }}">‹ Sebelumnya</a> @endif
    <span>Halaman {{ $laporan->currentPage() }} dari {{ $laporan->lastPage() }}</span>
    @if ($laporan->hasMorePages()) <a href="{{ $laporan->nextPageUrl() }}">Berikutnya ›</a> @else <span class="off">Berikutnya ›</span> @endif
  </div>
@endif

<a href="{{ route('presensi.index') }}" class="btn-outline">← Kembali ke Presensi</a>
@endsection
