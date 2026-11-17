<?php

namespace App\Services;

use App\Models\SuratKeterangan;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/** PDF Surat Keterangan selesai prakerin (dompdf bawaan di laravel/lib/dompdf, sama seperti Surat Balasan). */
class SuratKeteranganPdf
{
    public static function output(SuratKeterangan $surat): string
    {
        require_once base_path('lib/dompdf/autoload.inc.php');

        $surat->loadMissing('dataMagang');

        $cache = storage_path('framework/dompdf');
        if (! is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $options->set('chroot', base_path());
        $options->set('tempDir', is_writable($cache) ? $cache : sys_get_temp_dir());
        $options->set('fontCache', is_writable($cache) ? $cache : sys_get_temp_dir());

        $html = view('surat.keterangan-prakerin', [
            'surat'   => $surat,
            'cfg'     => config('prakerin.surat_keterangan'),
            'tanggal' => fn ($d) => SuratKeterangan::tanggalIndonesia($d),
        ])->render();

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }

    public static function respons(SuratKeterangan $surat, bool $inline = false): Response
    {
        $nama = 'Surat-Keterangan-Prakerin-' . (Str::slug($surat->dataMagang->nama) ?: 'peserta') . '.pdf';

        return response(self::output($surat), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $nama . '"',
        ]);
    }
}
