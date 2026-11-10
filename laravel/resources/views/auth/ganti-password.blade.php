@extends('layouts.auth')

@section('judul', 'Ganti Kata Sandi')
@section('body-class', 'mode-form')
@section('badge', 'Keamanan Akun')

@push('css')
<style>
/* Tambahan khusus halaman ganti sandi (sisanya memakai gaya layout login) */
.msg.info{background:#fffbeb;color:#92400e;border:1px solid #fde68a}
.hint{font-size:11.5px;color:#94a3b8;margin-top:5px}
.btn-link{display:block;width:100%;margin-top:14px;padding:6px;background:none;border:0;font:600 12.5px Inter,sans-serif;color:var(--gy);cursor:pointer;text-align:center}
.btn-link:hover{color:var(--b1);text-decoration:underline}
/* halaman wajib ganti sandi: sembunyikan tombol kembali ke landing di mobile */
.back{display:none!important}
</style>
@endpush

@section('konten')
<h2>Ganti Kata Sandi</h2>
<p class="sub">
    @if($wajib)
        Anda masuk dengan kata sandi awal. Buat kata sandi baru sebelum melanjutkan.
    @else
        Masukkan kata sandi lama dan kata sandi baru Anda.
    @endif
</p>

@if(session('info'))
    <div class="msg info">{{ session('info') }}</div>
@endif

<form method="POST" action="{{ route('password.simpan') }}">
    @csrf

    @unless($wajib)
        <div class="fld">
            <label>Kata Sandi Lama</label>
            <div class="inp"><span>🔑</span>
                <input type="password" name="password_lama" placeholder="••••••••" required autocomplete="current-password" class="@error('password_lama') error @enderror">
            </div>
        </div>
    @endunless

    <div class="fld">
        <label>Kata Sandi Baru</label>
        <div class="inp"><span>🔒</span>
            <input type="password" name="password" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password" class="@error('password') error @enderror">
        </div>
        <div class="hint">Minimal 8 karakter dan tidak boleh sama dengan kata sandi awal.</div>
    </div>

    <div class="fld">
        <label>Ulangi Kata Sandi Baru</label>
        <div class="inp"><span>🔒</span>
            <input type="password" name="password_confirmation" placeholder="Ketik ulang kata sandi baru" required autocomplete="new-password">
        </div>
    </div>

    <button class="btn" type="submit">Simpan Kata Sandi →</button>
    <button type="submit" form="form-keluar" class="btn-link">Keluar</button>
</form>

<form id="form-keluar" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
@endsection