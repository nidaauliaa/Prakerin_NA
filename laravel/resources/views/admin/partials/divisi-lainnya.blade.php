{{-- Isian tambahan kalau admin memilih "Lainnya" di dropdown posisi (section belum ada di daftar). --}}
{{-- Butuh: $departemen (daftar departemen). Dropdown-nya harus punya id="fDivisi". --}}
<div id="blokLainnya" class="hidden mt-3 p-3 rounded-xl bg-blue-50/60 border border-blue-100 space-y-3">
    <p class="text-[11px] text-slate-600 leading-relaxed">
        <i class="fa-solid fa-circle-info text-brand-blue mr-1"></i>
        Section ini akan ikut tersimpan ke daftar, jadi nanti bisa langsung dipilih dari dropdown.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Section Baru <span class="text-rose-500">*</span></label>
            <input type="text" name="divisi_baru_nama" id="fDivisiBaru" maxlength="150" value="{{ old('divisi_baru_nama') }}" placeholder="Contoh: Quality Control"
                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen <span class="text-rose-500">*</span></label>
            <select name="divisi_baru_departemen_id" id="fDivisiBaruDept"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                <option value="">- Pilih departemen -</option>
                @foreach ($departemen as $d)
                    <option value="{{ $d->id }}" @selected((string) old('divisi_baru_departemen_id') === (string) $d->id)>{{ $d->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<script>
    // Tampilkan/sembunyikan isian "Lainnya" mengikuti pilihan dropdown posisi.
    function toggleLainnya() {
        const aktif = document.getElementById('fDivisi').value === 'lainnya';
        document.getElementById('blokLainnya').classList.toggle('hidden', !aktif);
        document.getElementById('fDivisiBaru').required = aktif;
        document.getElementById('fDivisiBaruDept').required = aktif;
    }
    document.getElementById('fDivisi').addEventListener('change', toggleLainnya);
    toggleLainnya();
</script>
