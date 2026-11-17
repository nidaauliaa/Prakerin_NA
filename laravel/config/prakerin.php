<?php

// Pengaturan ekosistem presensi prakerin. Semua angka aturan ditaruh di sini
// supaya mudah diubah (dan mudah dipindah ke aplikasi utama) tanpa menyentuh controller.

return [

    // Jendela waktu sesi (jam, zona waktu mengikuti APP_TIMEZONE = Asia/Jakarta). Update HRD terbaru.
    'jendela' => [
        'pagi' => ['mulai' => 6, 'selesai' => 13],
        'sore' => ['mulai' => 13, 'selesai' => 18],
    ],

    // Popup tombol "Laporan" di halaman Presensi admin:
    // true  = ada tombol Tolak / Setujui di kanan bawah popup (hanya muncul di tanggal hari ini; tanggal lampau hanya tampilan riwayat).
    // false = popup hanya menampilkan selfie + laporan; ACC tetap lewat kolom "Catatan Harian" seperti sebelumnya.
    'laporan_popup_acc' => true,

    // Hari kerja memakai nomor hari ISO: 1 = Senin ... 7 = Minggu. Sekarang Senin-Jumat.
    'hari_kerja' => [1, 2, 3, 4, 5],

    // Tanggal libur nasional / cuti bersama (format 'Y-m-d'). Diisi manual dulu;
    // nanti bisa diganti tabel kalender perusahaan.
    'libur' => [
        // '2026-12-25',
    ],

    // Export Rekap Absensi (Excel bulanan, isi sel 1 / 0):
    // true  = hari dengan sesi pagi + sore baru dihitung MASUK (1) setelah sore di-ACC admin. Sama dengan
    //         persentase "Kehadiran Bulan Ini" di halaman Presensi (bawaan).
    // false = pagi + sore yang belum di-ACC tetap dihitung MASUK (1).
    'rekap_wajib_acc' => true,

    // Batas akurasi GPS (meter). Dicek di peramban DAN di server.
    // CATATAN: 7 m itu ketat -- banyak HP hanya mencapai 10-20 m di dalam bangunan. Longgarkan kalau banyak peserta gagal scan.
    'maks_akurasi_meter' => (float) env('PRAKERIN_MAKS_AKURASI', 10),

    // Batas ukuran foto selfie (KB) setelah di-decode.
    'maks_foto_kb' => 2048,

    // Titik pos absen yang sah -- GANTI koordinat & radius sesuai lokasi asli perusahaan.
    'pos' => [
        ['kode' => 'pos-1', 'nama' => 'Pos 1', 'lat' => -7.5062152, 'lng' => 110.2247733, 'radius' => 35],
        ['kode' => 'pos-2', 'nama' => 'Pos 2', 'lat' => -7.5043105, 'lng' => 110.2265946, 'radius' => 79],
        ['kode' => 'pos-3', 'nama' => 'Pos 3', 'lat' => -7.5021834, 'lng' => 110.2286008, 'radius' => 43],
        ['kode' => 'pos-4', 'nama' => 'Pos 4', 'lat' => -7.5021392, 'lng' => 110.2271552, 'radius' => 24],
        ['kode' => 'pos-5', 'nama' => 'Pos 5', 'lat' => -7.5051948, 'lng' => 110.2254398, 'radius' => 10],
    ],

    // Satu peserta hanya boleh absen di SATU zona (pos) yang ditentukan admin (menu Zona Absen).
    // Jangan ubah 'kode' pos yang sudah dipakai peserta (nama/koordinat/radius boleh diubah).
    // false = peserta TANPA zona memakai aturan lama (pos terdekat dari semua pos). Peserta yang sudah
    //         diberi zona tetap dibatasi ke zonanya. Aman dipakai selama zona belum diisi semua.
    // true  = peserta tanpa zona tidak bisa absen sampai admin menentukan zonanya.
    'zona_wajib' => true,

    // Anti lokasi palsu (fake GPS). Sifatnya "berlapis", bukan jaminan: browser tidak bisa mendeteksi
    // aplikasi fake GPS secara langsung. Lihat AttendanceController::cekGpsPalsu().
    // Sinyal kuat memblokir presensi; sinyal lemah menahan presensi sebagai "Menunggu ACC" admin.
    'anti_gps_palsu' => true,

    // Akurasi di bawah nilai ini (meter) dianggap tidak wajar dan presensinya diblokir. GPS HP asli di browser
    // biasanya 3-20 m; fake GPS umumnya melaporkan 1 m. Turunkan (mis. 1.5) bila peserta asli ikut terblokir.
    'akurasi_min_meter' => 2.0,

    // Opsional (paling efektif bila ada Wi-Fi kantor dengan IP publik tetap): presensi hanya diterima
    // dari IP/CIDR berikut, mis. ['203.0.113.10', '198.51.100.0/24']. Kosong = tidak dibatasi.
    'ip_presensi' => [],

    // Peran yang boleh melihat foto selfie & berkas tugas milik peserta lain.
    'peran_peninjau' => ['mentor', 'admin', 'superadmin'],

    // Peran yang boleh membuka halaman cetak kartu ID.
    'peran_admin' => ['admin', 'superadmin'],

    // status_akun peserta setelah admin menautkannya ke data Excel (lewat NIM/NISN).
    'status_setelah_tautan' => 'aktif',

    // ---- Tambahan deteksi lokasi palsu (14 Okt). Semuanya bisa dimatikan/disetel di sini. ----
    // Pergeseran (simpangan baku) koordinat antar sampel GPS, dalam meter. GPS asli selalu "bergoyang".
    // Di bawah nilai ini (tetapi tidak persis identik) dianggap tidak alami -> presensi ditahan untuk ACC.
    'jitter_min_meter' => 0.05,

    // Umur maksimum data lokasi (detik) saat presensi dikirim. Lebih tua dari ini = data basi/cache -> ditahan.
    'lokasi_basi_detik' => 180,

    // Jarak (meter) ke titik pusat zona yang dianggap "terlalu persis" -> ditahan untuk ACC.
    // Aplikasi fake GPS sering memasang koordinat tepat di pusat pos. Isi 0 untuk mematikan.
    'pusat_zona_meter' => 1.5,

    // Koordinat persis sama (sampai ~2 cm) dengan presensi peserta itu di HARI LAIN.
    // GPS asli hampir tidak pernah mengulang titik yang sama. 1 hari sama = ditahan, 2 hari atau lebih = diblokir.
    'koordinat_ulang_cek' => true,

    // Satu perangkat (device_id di browser) dipakai oleh akun peserta lain dalam 24 jam terakhir
    // -> presensi ditahan untuk ACC (dugaan titip absen). Tidak pernah memblokir, supaya perangkat kantor bersama aman.
    'perangkat_bersama_cek' => true,

    // ---- Surat Balasan Ijin Prakerin (PDF yang diunduh pelamar) ----
    'surat_balasan' => [
        'kota'          => 'Magelang',
        'kode_nomor'    => 'PRAKERIN/HCD/MAJ',      // No.: <nomor>/PRAKERIN/HCD/MAJ/<bulan romawi>/<tahun>
        'perusahaan'    => 'PT MEKAR ARMADA JAYA',
        'penandatangan' => 'Bambang Sulistiyo',
        'jabatan'       => 'HC Dev. & SHE Manager',
        'jam_induction' => '09.00',
        'kop'           => true,                    // false = tanpa baris nama perusahaan di atas surat (kertas sudah berkop)
    ],

    // ---- Penilaian prakerin & Surat Keterangan ----
    'penilaian' => [
        // [batas bawah nilai, predikat]. Nilai akhir = rata-rata berbobot semua butir (0-100).
        'predikat' => [
            [90, 'Sangat Baik'],
            [80, 'Baik'],
            [70, 'Cukup'],
            [0,  'Kurang'],
        ],
    ],

    // Surat Keterangan selesai prakerin (PDF yang diunduh peserta dari Dashboard Peserta)
    'surat_keterangan' => [
        'kota'          => 'Magelang',
        'kode_nomor'    => 'HCD/MAJ/PRAKERIN',      // No.: <nomor>/HCD/MAJ/PRAKERIN/<bulan romawi>/<tahun>
        'perusahaan'    => 'PT MEKAR ARMADA JAYA',
        'penandatangan' => 'Bambang Sulistiyo',
        'jabatan'       => 'HC Dev & SHE Manager',
        'kop'           => true,                    // false = tanpa baris nama perusahaan (kertas sudah berkop)
    ],

];
