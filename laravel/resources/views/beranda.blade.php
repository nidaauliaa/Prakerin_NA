@extends('layouts.public')

@section('content')
@if (session('lamaran_berhasil'))
    <div id="toastSukses" class="fixed top-24 right-4 z-[60] max-w-sm w-[calc(100%-2rem)] bg-white border border-emerald-200 shadow-xl rounded-2xl p-4 flex items-start space-x-3">
        <div class="w-9 h-9 shrink-0 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="text-xs text-slate-600 leading-relaxed flex-1">
            <p class="font-bold text-slate-900 text-sm">Lamaran berhasil dikirim!</p>
            Posisi <strong>{{ session('lamaran_berhasil.posisi') }}</strong>.<br>
            {{-- Kode lamaran Anda: <strong class="text-brand-blue">{{ session('lamaran_berhasil.kode') }}</strong><br> --}}
            Periode: <strong>{{ session('lamaran_berhasil.mulai') }} s/d {{ session('lamaran_berhasil.selesai') }}</strong>.<br>
            Cek Dashboard Anda untuk melihat update lamaran.
            Surat Keterangan Ijin Prakerin dapat diunduh di Dashboard Peserta setelah lamaran disetujui oleh Admin PT Mekar Armada Jaya.
        </div>
        <button onclick="document.getElementById('toastSukses').remove()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if (session('info'))
    <div id="toastInfo" class="fixed top-24 right-4 z-[60] max-w-sm w-[calc(100%-2rem)] bg-white border border-amber-200 shadow-xl rounded-2xl p-4 flex items-start space-x-3">
        <div class="w-9 h-9 shrink-0 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div class="text-xs text-slate-600 leading-relaxed flex-1">{{ session('info') }}</div>
        <button onclick="document.getElementById('toastInfo').remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif

    <!-- HERO SECTION (Sesuai Desain Gambar 5) -->
    <section id="beranda" class="relative pt-16 pb-20 md:pt-24 md:pb-28 overflow-hidden bg-gradient-to-b from-slate-50 via-blue-50/30 to-slate-50">
        <div class="max-w-5xl mx-auto px-4 text-center relative z-10">
            <!-- Pill Badge -->
            <div class="inline-flex items-center space-x-2 bg-white px-4 py-1.5 rounded-full shadow-sm border border-slate-200 mb-8">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-blue animate-pulse"></span>
                <span class="text-xs font-bold text-brand-blue uppercase tracking-wider">New Armada</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                Let's build your future with us<br>
                <span class="text-brand-blue">Start your career</span> <span class="text-brand-red">with New Armada</span>
            </h1>

            <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto mb-10 font-medium">
                Together we are strong. Program Prakerin & Magang Industri PT Mekar Armada Jaya.
            </p>

            <div class="flex justify-center">
                <a href="#lowongan" class="inline-flex items-center space-x-2 bg-brand-blue hover:bg-brand-darkBlue text-white font-semibold px-7 py-3.5 rounded-xl shadow-lg shadow-blue-500/25 transition transform hover:-translate-y-0.5">
                    <span>Lihat Lowongan Tersedia</span>
                    <i class="fa-solid fa-arrow-down text-sm"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- PROFIL PERUSAHAAN (Gambar 1) -->
    <section id="profil" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Placeholder Image -->
                <div class="lg:col-span-6">
                    <div class="relative rounded-2xl bg-slate-200 h-80 sm:h-96 flex flex-col items-center justify-center border-2 border-dashed border-slate-300 p-6 text-center text-slate-500 shadow-inner">
                        <i class="fa-solid fa-building-user text-4xl text-slate-400 mb-3"></i>
                        <span class="font-semibold text-slate-600">Foto Gedung Perusahaan</span>
                        <span class="text-xs text-slate-400 mt-1">(Ganti dengan foto asli PT Mekar Armada Jaya)</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">TENTANG PERUSAHAAN</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 leading-tight">
                        Merintis keunggulan industri selama lima dekade.
                    </h2>
                    <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                        Sebagai holding management dari New Armada Group (NAG), PT Mekar Armada Jaya (MAJ) berkomitmen melakukan penyelarasan di semua anak perusahaan dalam lingkup berbagai aspek bisnis, pengelolaan sumber daya manusia, portofolio bisnis, dan lain-lain.
                    </p>
                    <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                        Ini merupakan tantangan menarik sekaligus berprospek tinggi mengingat New Armada Group memiliki kepemilikan saham di berbagai perusahaan yang bergerak di bidang manufaktur, trading automotive, keuangan dan perbankan, serta beberapa bidang lainnya.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- PERSYARATAN UMUM (Gambar 2) -->
    <section id="persyaratan" class="py-16 bg-slate-50 border-y border-slate-200">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl sm:text-3xl font-black text-center text-brand-red tracking-tight mb-10 uppercase">
                PERSYARATAN UMUM
            </h2>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-4">
                <ol class="space-y-4 text-slate-700 font-medium text-base sm:text-lg">
                    <li class="flex items-start space-x-3">
                        <span class="font-bold text-brand-red">1.</span>
                        <span>Siswa SMK minimal kelas XI / Mahasiswa minimal semester 5</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-bold text-brand-red">2.</span>
                        <span>Minimal durasi prakerin 3 bulan (tidak terganggu jadwal kuliah/sekolah)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-bold text-brand-red">3.</span>
                        <span>Bersedia magang/prakerin On Site di Magelang (5 hari kerja)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-bold text-brand-red">4.</span>
                        <span>Bersedia magang/prakerin Unpaid (tidak dapat uang saku)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-bold text-brand-red">5.</span>
                        <span>Bersedia membuat BPJS Ketenagakerjaan</span>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- DAFTAR LOWONGAN AKTIF (Gambar 3 - Disesuaikan) -->
    <section id="lowongan" class="py-20 bg-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header & Filter -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div class="flex items-center space-x-3">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Daftar Lowongan Aktif</h2>
                    <span id="jobCountBadge" class="bg-blue-100 text-brand-blue text-xs font-bold px-3 py-1 rounded-full">12 Posisi</span>
                </div>

                <!-- Filter Controls -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Input -->
                    <div class="relative min-w-[260px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" id="searchInput" onkeyup="filterJobs()" placeholder="Cari posisi prakerin atau jurusan..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue shadow-sm">
                    </div>

                    <!-- Category Pills -->
                    <div class="flex bg-slate-200 p-1 rounded-xl text-xs font-semibold">
                        <button onclick="setCategory('semua')" id="cat-semua" class="px-4 py-2 rounded-lg bg-slate-900 text-white shadow-sm transition">Semua</button>
                        <button onclick="setCategory('smk')" id="cat-smk" class="px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition">SMK</button>
                        <button onclick="setCategory('mahasiswa')" id="cat-mahasiswa" class="px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition">Mahasiswa (D3/S1)</button>
                    </div>
                </div>
            </div>

            <!-- Job Cards Grid -->
            <div id="jobGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Rendered dynamically by JS -->
            </div>
        </div>
    </section>

    <!-- GALERI MAGANG (Gambar 4) -->
    <section id="galeri" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10">
                <span class="text-xs font-bold text-brand-blue uppercase tracking-widest">DOKUMENTASI</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-1">Galeri Magang</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-slate-200 rounded-2xl h-56 flex items-center justify-center text-slate-500 font-medium border border-slate-300">
                    Foto Kegiatan 1
                </div>
                <div class="bg-slate-200 rounded-2xl h-56 flex items-center justify-center text-slate-500 font-medium border border-slate-300">
                    Foto Kegiatan 2
                </div>
                <div class="bg-slate-200 rounded-2xl h-56 flex items-center justify-center text-slate-500 font-medium border border-slate-300">
                    Foto Kegiatan 3
                </div>
                <div class="bg-slate-200 rounded-2xl h-56 flex items-center justify-center text-slate-500 font-medium border border-slate-300">
                    Foto Kegiatan 4
                </div>
            </div>
        </div>
    </section>

@endsection

@push('modals')
    <!-- MODAL DETAIL & LAMAR (WITH DAILY OCCUPANCY DATE CHECK) -->
    <div id="applyModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="p-6 bg-slate-50 border-b border-slate-200 flex items-start justify-between">
                <div>
                    <span id="modalCategory" class="inline-block px-2.5 py-0.5 rounded text-[11px] font-extrabold uppercase mb-1"></span>
                    <h3 id="modalTitle" class="text-xl font-bold text-slate-900"></h3>
                    <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> Kota Alokasi: <strong>Magelang (PT MAJ)</strong></p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form id="lamarForm" method="POST" action="#" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <input type="hidden" name="lowongan_id" id="lowonganId" value="{{ old('lowongan_id') }}">

            <!-- Modal Body -->
            <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
                <!-- Info Kuota & Jurusan -->
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kapasitas Maksimal</span>
                        <span id="modalMaxQuota" class="text-base font-extrabold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kualifikasi Jurusan</span>
                        <span id="modalMajor" class="text-xs font-semibold text-slate-700"></span>
                    </div>
                </div>

                <!-- Info Slot Terisi / Ter-booked saat ini -->
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Riwayat Slot Periode Terisi:</h4>
                    <div id="bookedPeriodsList" class="space-y-2 text-xs">
                        <!-- Filled dynamically -->
                    </div>
                </div>

                <!-- Date Selection Form -->
                <div class="border-t border-slate-200 pt-5 space-y-4">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-brand-blue"></i>
                        Pilih Periode Magang Yang Diajukan
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai (Start Date)</label>
                            <input type="date" id="startDate" name="tgl_mulai" required onchange="validateDates()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai (End Date)</label>
                            <input type="date" id="endDate" name="tgl_selesai" required onchange="validateDates()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                    </div>

                    <!-- Warning Alert Box -->
                    <div id="validationAlert" class="hidden p-3.5 rounded-xl text-xs flex items-start space-x-2.5">
                        <i id="alertIcon" class="fa-solid fa-circle-info text-base mt-0.5"></i>
                        <div id="alertMessage" class="leading-relaxed"></div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="p-3.5 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200">
                        <strong>Periode belum bisa diajukan, mohon periksa:</strong>
                        <ul class="list-disc ml-4 mt-1 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="button" id="submitBtn" onclick="kirimLamaran()" disabled class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-300 cursor-not-allowed transition">Kirim Lamaran</button>
            </div>
            </form>
        </div>
    </div>
@endpush

@push('modals')
    @include('lamaran._persyaratan')
@endpush

@push('scripts')
    <script>
        // DATA LOWONGAN PERMANEN (1 CARD = 1 POSISI UNIK)
        const jobList = @json($jobs);
        const punyaProfil = @json($punyaProfil);

        // Sudah punya profil -> langsung popup persyaratan lalu lamaran terkirim (tanpa isi data diri lagi).
        // Belum punya profil -> lanjut ke login / form data diri (popup persyaratan muncul di form itu).
        function kirimLamaran() {
            const form = document.getElementById('lamarForm');
            if (punyaProfil) { bukaPersyaratan(form); } else { form.submit(); }
        }

        let activeCategory = 'semua';
        let selectedJob = null;

        // Render Card Function
        function renderJobs() {
            const grid = document.getElementById('jobGrid');
            const searchVal = document.getElementById('searchInput').value.toLowerCase();
            grid.innerHTML = '';

            const filtered = jobList.filter(job => {
                const matchCategory = activeCategory === 'semua' || job.category === activeCategory;
                const matchSearch = job.title.toLowerCase().includes(searchVal) || job.major.toLowerCase().includes(searchVal);
                return matchCategory && matchSearch;
            });

            document.getElementById('jobCountBadge').innerText = `${filtered.length} Posisi`;

            filtered.forEach(job => {
                const isMahasiswa = job.category === 'mahasiswa';
                const badgeBg = isMahasiswa ? 'bg-blue-100 text-brand-blue' : 'bg-amber-100 text-amber-700';

                const cardHtml = `
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <!-- Header Card -->
                            <div class="flex flex-wrap items-center justify-between gap-1.5 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold ${badgeBg}">
                                    ${job.categoryLabel}
                                </span>
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200/50">
                                    Kuota: ${job.maxQuota} Orang
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-slate-900 mb-1 leading-snug">${job.title}</h3>
                            <p class="text-xs text-slate-500 mb-3"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> Kota Alokasi: <strong>Magelang</strong> (PT MAJ)</p>

                            <!-- Description -->
                            <p class="text-xs text-slate-600 leading-relaxed mb-4 line-clamp-3">${job.description}</p>

                            <!-- Qualification Tag -->
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 mb-4">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">JURUSAN:</span>
                                <span class="text-xs font-semibold text-slate-700">${job.major}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button onclick="openModal(${job.id})" class="w-full py-2.5 bg-slate-900 hover:bg-brand-blue text-white text-xs font-bold rounded-xl transition flex items-center justify-center space-x-2">
                            <span>Detail & Lamar</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                `;
                grid.innerHTML += cardHtml;
            });
        }

        // Category Filter
        function setCategory(cat) {
            activeCategory = cat;
            ['semua', 'smk', 'mahasiswa'].forEach(c => {
                const btn = document.getElementById(`cat-${c}`);
                if (c === cat) {
                    btn.className = "px-4 py-2 rounded-lg bg-slate-900 text-white shadow-sm transition";
                } else {
                    btn.className = "px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition";
                }
            });
            renderJobs();
        }

        function filterJobs() {
            renderJobs();
        }

        // URL pilih periode (id posisi diganti saat modal dibuka)
        const pilihUrlTemplate = @json(route('lamaran.pilih', ['lowongan' => '__ID__']));

        // Modal Open
        function openModal(jobId) {
            selectedJob = jobList.find(j => j.id === jobId);
            if (!selectedJob) return;

            document.getElementById('modalTitle').innerText = selectedJob.title;
            document.getElementById('modalCategory').innerText = selectedJob.categoryLabel;
            document.getElementById('modalCategory').className = selectedJob.category === 'mahasiswa' 
                ? 'inline-block px-2.5 py-0.5 rounded text-[11px] font-extrabold uppercase mb-1 bg-blue-100 text-brand-blue'
                : 'inline-block px-2.5 py-0.5 rounded text-[11px] font-extrabold uppercase mb-1 bg-amber-100 text-amber-700';

            document.getElementById('modalMaxQuota').innerText = `${selectedJob.maxQuota} Orang`;
            document.getElementById('modalMajor').innerText = selectedJob.major;
            document.getElementById('lamarForm').action = pilihUrlTemplate.replace('__ID__', selectedJob.id);
            document.getElementById('lowonganId').value = selectedJob.id;

            // Render Booked Ranges
            const bookedList = document.getElementById('bookedPeriodsList');
            bookedList.innerHTML = '';

            if (selectedJob.bookedRanges.length === 0) {
                bookedList.innerHTML = `<p class="text-emerald-600 font-medium italic"><i class="fa-solid fa-circle-check mr-1"></i> Belum ada periode yang terisi penuh. Kuota masih sangat fleksibel!</p>`;
            } else {
                selectedJob.bookedRanges.forEach((range, idx) => {
                    bookedList.innerHTML += `
                        <div class="flex items-center justify-between bg-amber-50 border border-amber-200 p-2.5 rounded-lg text-amber-900">
                            <span><i class="fa-regular fa-calendar-xmark text-amber-600 mr-1.5"></i> Terisi: <strong>${formatDateStr(range.start)}</strong> s/d <strong>${formatDateStr(range.end)}</strong></span>
                            <span class="bg-amber-200 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded">Terisi (1 Slot)</span>
                        </div>
                    `;
                });
            }

            // Reset inputs
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('validationAlert').className = 'hidden';
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.className = "px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-300 cursor-not-allowed transition";

            document.getElementById('applyModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('applyModal').classList.add('hidden');
        }

        // DAILY OCCUPANCY DATE VALIDATION ENGINE
        function validateDates() {
            const startVal = document.getElementById('startDate').value;
            const endVal = document.getElementById('endDate').value;
            const alertBox = document.getElementById('validationAlert');
            const alertMsg = document.getElementById('alertMessage');
            const alertIcon = document.getElementById('alertIcon');
            const submitBtn = document.getElementById('submitBtn');

            if (!startVal || !endVal) {
                alertBox.className = 'hidden';
                return;
            }

            const start = new Date(startVal);
            const end = new Date(endVal);

            // 1. Cek Tanggal Selesai > Tanggal Mulai
            if (end <= start) {
                alertBox.className = 'p-3.5 rounded-xl text-xs flex items-start space-x-2.5 bg-rose-50 text-rose-800 border border-rose-200';
                alertIcon.className = 'fa-solid fa-circle-xmark text-base text-rose-600 mt-0.5';
                alertMsg.innerHTML = '<strong>Tanggal Tidak Valid:</strong> Tanggal selesai harus lebih besar dari tanggal mulai.';
                disableSubmit();
                return;
            }

            // 2. Cek Durasi Minimal 3 Bulan (90 Hari)
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 90) {
                alertBox.className = 'p-3.5 rounded-xl text-xs flex items-start space-x-2.5 bg-amber-50 text-amber-800 border border-amber-200';
                alertIcon.className = 'fa-solid fa-triangle-exclamation text-base text-amber-600 mt-0.5';
                alertMsg.innerHTML = `<strong>Durasi Kurang:</strong> Durasi magang yang Anda pilih hanya <strong>${diffDays} hari</strong>. Persyaratan minimal prakerin adalah <strong>3 Bulan (90 hari)</strong>.`;
                disableSubmit();
                return;
            }

            // 3. Cek Bentrok Kuota Per Hari (Daily Occupancy Engine)
            let maxOccupancyFound = 0;
            let overflowStartDate = null;
            let overflowEndDate = null;

            // Iterate through each day of requested range
            let currentDate = new Date(start);
            while (currentDate <= end) {
                let countForDay = 0;
                
                // Count how many existing bookings cover this currentDate
                selectedJob.bookedRanges.forEach(b => {
                    const bStart = new Date(b.start);
                    const bEnd = new Date(b.end);
                    if (currentDate >= bStart && currentDate <= bEnd) {
                        countForDay++;
                    }
                });

                if (countForDay >= selectedJob.maxQuota) {
                    if (!overflowStartDate) overflowStartDate = new Date(currentDate);
                    overflowEndDate = new Date(currentDate);
                }

                if (countForDay > maxOccupancyFound) {
                    maxOccupancyFound = countForDay;
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Jika pada rentang tanggal tersebut kuota harian melebihi kapasitas
            if (maxOccupancyFound >= selectedJob.maxQuota) {
                alertBox.className = 'p-3.5 rounded-xl text-xs flex items-start space-x-2.5 bg-rose-50 text-rose-800 border border-rose-200';
                alertIcon.className = 'fa-solid fa-calendar-xmark text-base text-rose-600 mt-0.5';
                alertMsg.innerHTML = `
                    <strong>PERIODE PENUH (KUOTA TERKUNCI):</strong><br>
                    Pada periode <strong>${formatDate(overflowStartDate)} s/d ${formatDate(overflowEndDate)}</strong>, kuota telah terisi penuh (<strong>${selectedJob.maxQuota}/${selectedJob.maxQuota} orang</strong>).<br>
                    <span class="text-rose-700 font-semibold mt-1 block">💡 Saran: Silakan geser tanggal mulai/selesai Anda di luar rentang tanggal penuh tersebut.</span>
                `;
                disableSubmit();
                return;
            }

            // 4. Jika Lolos Semua Validasi
            alertBox.className = 'p-3.5 rounded-xl text-xs flex items-start space-x-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200';
            alertIcon.className = 'fa-solid fa-circle-check text-base text-emerald-600 mt-0.5';
            alertMsg.innerHTML = `
                <strong>PERIODE TERSEDIA!</strong><br>
                Durasi magang: <strong>${diffDays} hari (~${Math.round(diffDays/30)} Bulan)</strong>. Tanggal yang Anda pilih aman dan kuota masih tersedia.
            `;
            
            enableSubmit();
        }

        function disableSubmit() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.className = "px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-300 cursor-not-allowed transition";
        }

        function enableSubmit() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.className = "px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-blue hover:bg-brand-darkBlue shadow-md shadow-blue-500/20 transition";
        }

        function formatDate(d) {
            if (!d) return '';
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        function formatDateStr(str) {
            return formatDate(new Date(str));
        }

        // Initialize on load
        window.onload = function() {
            renderJobs();
            @if ($errors->any() && old('lowongan_id'))
            // Validasi server gagal: buka kembali modal posisi yang sama
            openModal({{ (int) old('lowongan_id') }});
            document.getElementById('startDate').value = @json(old('tgl_mulai'));
            document.getElementById('endDate').value = @json(old('tgl_selesai'));
            validateDates();
            @endif
        };
    </script>
@endpush