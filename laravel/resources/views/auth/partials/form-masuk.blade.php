<h2>Masuk ke Akun Anda</h2>
<p class="sub">Masukkan NIS/NIM &amp; kata sandi yang diberikan admin</p>

<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <div class="fld">
        <label>NIS / NIM</label>
        <div class="inp"><span>#</span>
            <input name="identifier" value="{{ old('identifier') }}" placeholder="Masukkan NIS / NIM" autocomplete="username" class="@error('identifier') error @enderror" required>
        </div>
        @error('identifier') <div class="errtxt">{{ $message }}</div> @enderror
    </div>
    <div class="fld">
        <label>Kata Sandi <a href="{{ route('lupa-sandi') }}">Lupa kata sandi?</a></label>
        <div class="inp"><span>🔒</span>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
    </div>
    <label class="chk"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label>
    <button class="btn" type="submit">Masuk ke Akun →</button>
</form>

<p class="switch">Akun dibuat oleh admin. Belum punya akun? Hubungi admin.</p>