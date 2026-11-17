<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Surat Keterangan Prakerin - {{ $surat->dataMagang->nama }}</title>
<style>
    @page { margin: 2.2cm 2.5cm 2cm 2.5cm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11pt; line-height: 1.45; color: #000; }
    p { margin: 0 0 10px 0; text-align: justify; }
    .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 18px; }
    .kop strong { font-size: 14pt; letter-spacing: 1px; }
    .sk-judul { text-align: center; font-size: 14pt; text-decoration: underline; margin: 0 0 4px 0; }
    .sk-nomor { text-align: center; margin-bottom: 22px; }
    table.sk-peserta { width: 100%; border-collapse: collapse; margin: 4px 0 14px 0; }
    table.sk-peserta th, table.sk-peserta td { border: 1px solid #000; padding: 6px 8px; text-align: center; vertical-align: middle; }
    table.sk-peserta th { background: #efefef; font-size: 10pt; }
    .sk-tengah { text-align: center; }
    .sk-ttd { margin-top: 28px; }
    .sk-ttd p { text-align: left; }
    .sk-ruang-ttd { height: 70px; }
</style>
</head>
<body>

@if (! empty($cfg['kop']))
    <div class="kop"><strong>{{ $cfg['perusahaan'] }}</strong></div>
@endif

@include('surat.partials.keterangan-isi')

</body>
</html>
