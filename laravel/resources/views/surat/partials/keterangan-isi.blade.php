{{-- Isi Surat Keterangan. Dipakai oleh PDF (surat.keterangan-prakerin) dan pratinjau di Dashboard Peserta.
     Perlu: $surat (SuratKeterangan, relasi dataMagang termuat), $cfg, $tanggal (closure). --}}
@php
    $dm      = $surat->dataMagang;
    $smk     = strtolower((string) $dm->tipe_magang) === 'smk';
    $sebagai = $smk ? 'SISWA' : 'MAHASISWA';
@endphp

<h2 class="sk-judul">SURAT KETERANGAN</h2>
<p class="sk-nomor">No.{{ $surat->nomorLengkap() }}</p>

<p>Yang bertanda tangan di bawah ini menerangkan bahwa <strong>{{ $sebagai }} {{ mb_strtoupper($dm->asal_sekolah) }}</strong> atas nama:</p>

<table class="sk-peserta">
    <tr>
        <th style="width: 38%;">NAMA</th>
        <th style="width: 31%;">JURUSAN</th>
        <th>PENEMPATAN</th>
    </tr>
    <tr>
        <td>{{ $dm->nama }}</td>
        <td>{{ $dm->jurusan }}</td>
        <td>{{ $surat->penempatan }}</td>
    </tr>
</table>

<p>
    Telah melaksanakan <strong>Praktek Kerja Industri (Prakerin)</strong> untuk mendapatkan ilmu dan pengalaman kerja di
    <strong>PT Mekar Armada Jaya, {{ $cfg['kota'] }}.</strong> Dengan predikat penilaian:
</p>

<p class="sk-tengah"><strong>{{ $surat->teksNilai() }}</strong></p>

<p style="margin-bottom: 0;">Praktek Kerja Industri tersebut dilaksanakan pada tanggal:</p>
<p class="sk-tengah"><strong>{{ $tanggal($surat->tgl_mulai) }} s/d {{ $tanggal($surat->tgl_selesai) }}</strong></p>

<p>Demikian surat keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.</p>

<div class="sk-ttd">
    <p style="margin-bottom: 0;">{{ $cfg['kota'] }}, {{ $tanggal($surat->tanggal_surat) }}</p>
    <p style="margin-bottom: 0;"><strong>{{ $cfg['perusahaan'] }}</strong></p>
    <div class="sk-ruang-ttd"></div>
    <p style="margin-bottom: 0;"><strong><u>{{ $cfg['penandatangan'] }}</u></strong></p>
    <p style="margin-bottom: 0;"><strong>{{ $cfg['jabatan'] }}</strong></p>
</div>
