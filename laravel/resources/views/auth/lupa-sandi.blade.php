@extends('layouts.auth')

@section('judul', 'Lupa Kata Sandi')
@section('body-class', 'mode-form')
@section('badge', 'Bantuan Akun')

@push('css')
<style>
.fld textarea{width:100%;padding:12px 14px;border:1.5px solid var(--bd);border-radius:11px;font:14px Inter,sans-serif;background:#fafbfd;resize:vertical;min-height:96px}
.fld textarea:focus{outline:0;border-color:var(--b1);background:#fff}
.fld textarea.error{border-color:#dc2626}
.hint{font-size:11.5px;color:#94a3b8;margin-top:5px}
</style>
@endpush

@section('konten')
<h2>Lupa Kata Sandi</h2>
<p class="sub">Isi data berikut. Permintaanmu dikirim ke admin, lalu admin mereset kata sandi akunmu.</p>

<form method="POST" action="{{ route('lupa-sandi.kirim') }}">
    @csrf
    <div class="fld">
        <label>Nama Lengkap</label>
        <div class="inp"><span>👤</span>
            <input name="nama" value="{{ old('nama') }}" placeholder="Nama sesuai data magang" autocomplete="name" class="@error('nama') error @enderror" required>
        </div>
        @error('nama') <div class="errtxt">{{ $message }}</div> @enderror
    </div>
    <div class="fld">
        <label>NIS / NIM</label>
        <div class="inp"><span>#</span>
            <input name="nim" value="{{ old('nim') }}" placeholder="Masukkan NIS / NIM" autocomplete="username" class="@error('nim') error @enderror" required>
        </div>
        @error('nim') <div class="errtxt">{{ $message }}</div> @enderror
    </div>
    <div class="fld">
        <label>Divisi</label>
        <div class="inp"><span>🏢</span>
            <input name="divisi" value="{{ old('divisi') }}" list="daftarDivisi" placeholder="Pilih atau ketik divisi" class="@error('divisi') error @enderror" required>
        </div>
        <datalist id="daftarDivisi">
            @foreach($departemen as $d)<option value="{{ $d->nama }}">@endforeach
        </datalist>
        @error('divisi') <div class="errtxt">{{ $message }}</div> @enderror
    </div>
    <div class="fld">
        <label>Alasan Lupa Kata Sandi</label>
        <textarea name="alasan" maxlength="500" placeholder="Contoh: Sudah lama tidak login, lupa sandi yang saya buat sendiri." class="@error('alasan') error @enderror" required>{{ old('alasan') }}</textarea>
        @error('alasan') <div class="errtxt">{{ $message }}</div> @enderror
        <div class="hint">Riwayat absen dan tugasmu tidak hilang saat sandi direset.</div>
    </div>
    <button class="btn" type="submit">Kirim ke Admin →</button>
</form>

<p class="switch" style="margin-top:18px"><a href="{{ route('login') }}">← Kembali ke halaman masuk</a></p>
@endsection