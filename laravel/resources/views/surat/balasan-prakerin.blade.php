<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Balasan Ijin Pengajuan Prakerin - {{ $lamaran->nama }}</title>
<style>
    @page { margin: 2cm 2.5cm 2cm 2.5cm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11pt; line-height: 1.4; color: #000; }
    p { margin: 0 0 9px 0; text-align: justify; }
    .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 14px; }
    .kop strong { font-size: 14pt; letter-spacing: 1px; }
    table { border-collapse: collapse; }
    table.meta td { padding: 0; vertical-align: top; }
    table.peserta { width: 100%; margin: 4px 0 12px 0; }
    table.peserta th, table.peserta td { border: 1px solid #000; padding: 5px 8px; text-align: left; vertical-align: top; }
    table.peserta th { background: #efefef; font-size: 10pt; }
    .tengah { text-align: center; }
    .ttd { margin-top: 4px; }
    .ruang-ttd { height: 62px; }
</style>
</head>
<body>

@if (! empty($cfg['kop']))
    <div class="kop"><strong>{{ $cfg['perusahaan'] }}</strong></div>
@endif

<p style="text-align:left;">{{ $cfg['kota'] }}, {{ $tanggal($surat->tanggal_surat) }}</p>

<table class="meta" style="margin-bottom: 12px;">
    <tr><td style="width: 38px;">No.</td><td style="width: 10px;">:</td><td>{{ $surat->nomorLengkap() }}</td></tr>
    <tr><td>Hal</td><td>:</td><td>Balasan Ijin Pengajuan Prakerin</td></tr>
</table>

<p style="text-align:left; margin-bottom: 0;">Kepada Yth,</p>
<p style="text-align:left; margin-bottom: 0;">Bapak/Ibu {{ $surat->ditujukan_kepada }}</p>
<p style="text-align:left; margin-bottom: 12px;">Di tempat</p>

<p>Dengan hormat,</p>

<p>
    Berdasarkan pengajuan Permohonan Kerja Praktek Industri dari {{ $lamaran->asal_sekolah }}
    pada tanggal {{ $tanggal($surat->tgl_pengajuan) }} melalui Surat {{ $surat->media_pengajuan }}
    Ke PT. Mekar Armada Jaya, maka dapat kami sampaikan bahwa Peserta berikut atas nama:
</p>

<table class="peserta">
    <tr>
        <th style="width: 38%;">NAMA</th>
        <th style="width: 22%;">NPM/NIS</th>
        <th>PROGRAM STUDI</th>
    </tr>
    <tr>
        <td>{{ $lamaran->nama }}</td>
        <td>{{ $lamaran->nis_nim ?: '-' }}</td>
        <td>{{ $lamaran->jurusan }}</td>
    </tr>
</table>

<p>DAPAT DITERIMA di PT MEKAR ARMADA JAYA Magelang. Adapun pelaksanaan dimulai</p>

<p class="tengah"><strong>{{ $tanggal($surat->tgl_mulai) }} s/d {{ $tanggal($surat->tgl_selesai) }}</strong></p>

<p>
    Kepada peserta yang bersangkutan di atas, dimohon menyerahkan Foto berwarna ukuran 2 x 3 (2 lembar),
    FC kartu Mahasiswa/Pelajar yang masih berlaku (2 lembar), FC BPJS Ketenagakerjaan (2 lembar),
    FC KTP/KK (2 lembar). Peserta Prakerin wajib datang sesuai waktu yang telah ditentukan yaitu
    {{ $tanggal($surat->tgl_induction) }} pukul {{ $cfg['jam_induction'] }} WIB tepat, sesuai tanggal masuk
    untuk melakukan Training introduction.
</p>

<p>Demikian kami sampaikan agar menjadi perhatian, Terima kasih.</p>

<div class="ttd">
    <p style="text-align:left; margin-bottom: 0;">Hormat Kami,</p>
    <div class="ruang-ttd"></div>
    <p style="text-align:left; margin-bottom: 0;"><strong><u>{{ $cfg['penandatangan'] }}</u></strong></p>
    <p style="text-align:left; margin-bottom: 0;">{{ $cfg['jabatan'] }}</p>
</div>

</body>
</html>
