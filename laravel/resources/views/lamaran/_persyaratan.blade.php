{{-- Popup Persyaratan Umum: muncul SETIAP kali pelamar mengirim lamaran (pertama maupun kedua). --}}
<div id="modalPersyaratan" class="fixed inset-0 z-[70] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="judulPersyaratan">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-6 bg-slate-50 border-b border-slate-200">
            <h3 id="judulPersyaratan" class="text-xl font-black text-brand-red tracking-tight uppercase">Persyaratan Umum</h3>
            <p class="text-xs text-slate-500 mt-1">Baca persyaratan berikut, lalu setujui untuk melanjutkan pengiriman lamaran.</p>
        </div>

        <div class="p-6 space-y-5">
            <ol class="space-y-3 text-sm text-slate-700 font-medium">
                <li class="flex items-start gap-3"><span class="font-bold text-brand-red">1.</span><span>Siswa SMK minimal kelas XI / Mahasiswa minimal semester 5</span></li>
                <li class="flex items-start gap-3"><span class="font-bold text-brand-red">2.</span><span>Minimal durasi prakerin 3 bulan (tidak terganggu jadwal kuliah/sekolah)</span></li>
                <li class="flex items-start gap-3"><span class="font-bold text-brand-red">3.</span><span>Bersedia magang/prakerin On Site di Magelang (5 hari kerja)</span></li>
                <li class="flex items-start gap-3"><span class="font-bold text-brand-red">4.</span><span>Bersedia magang/prakerin Unpaid (tidak dapat uang saku)</span></li>
                <li class="flex items-start gap-3"><span class="font-bold text-brand-red">5.</span><span>Bersedia membuat BPJS Ketenagakerjaan</span></li>
            </ol>

            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer select-none">
                <input type="checkbox" id="cekSetujuPersyaratan" class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                <span class="text-sm font-semibold text-slate-800">Saya menyetujui persyaratan umum ini</span>
            </label>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <button type="button" onclick="tutupPersyaratan()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
            <button type="button" id="btnLanjutPersyaratan" onclick="lanjutPersyaratan()" disabled
                    class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-300 cursor-not-allowed transition flex items-center gap-2">
                <span>Lanjut</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('modalPersyaratan');
        const cek   = document.getElementById('cekSetujuPersyaratan');
        const lanjut = document.getElementById('btnLanjutPersyaratan');
        let formTujuan = null;

        const gayaAktif = 'px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition flex items-center gap-2';
        const gayaMati  = 'px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-300 cursor-not-allowed transition flex items-center gap-2';

        function aturTombol() {
            lanjut.disabled = !cek.checked;
            lanjut.className = cek.checked ? gayaAktif : gayaMati;
        }

        // Dipanggil tombol "Kirim Lamaran": form tujuan baru dikirim setelah pelamar menyetujui.
        window.bukaPersyaratan = function (form) {
            formTujuan = form;
            cek.checked = false;
            aturTombol();
            modal.classList.remove('hidden');
        };

        window.tutupPersyaratan = function () {
            modal.classList.add('hidden');
            formTujuan = null;
        };

        window.lanjutPersyaratan = function () {
            if (!cek.checked || !formTujuan) return;
            let h = formTujuan.querySelector('input[name="setuju_persyaratan"]');
            if (!h) {
                h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'setuju_persyaratan';
                formTujuan.appendChild(h);
            }
            h.value = '1';
            lanjut.disabled = true;          // cegah klik ganda
            formTujuan.submit();
        };

        cek.addEventListener('change', aturTombol);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutupPersyaratan(); });
    })();
</script>
