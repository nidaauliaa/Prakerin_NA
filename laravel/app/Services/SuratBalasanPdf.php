<?php

namespace App\Services;

use App\Models\SuratBalasan;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Membuat PDF Surat Balasan Ijin Prakerin memakai dompdf.
 * Library ada di laravel/lib/dompdf (rilis bawaan dompdf, sudah lengkap dengan dependensinya),
 * jadi tidak perlu `composer require` di hosting.
 */
class SuratBalasanPdf
{
    public static function output(SuratBalasan $surat): string
    {
        require_once base_path('lib/dompdf/autoload.inc.php');

        $surat->loadMissing('lamaran');

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

        $html = view('surat.balasan-prakerin', [
            'surat'    => $surat,
            'lamaran'  => $surat->lamaran,
            'cfg'      => config('prakerin.surat_balasan'),
            'tanggal'  => fn ($d) => SuratBalasan::tanggalIndonesia($d),
        ])->render();

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }

    /** Respons unduhan (attachment) atau tampil di tab baru (inline) untuk pratinjau admin. */
    public static function respons(SuratBalasan $surat, bool $inline = false): Response
    {
        $nama = 'Surat-Balasan-Prakerin-' . (Str::slug($surat->lamaran->nama) ?: 'peserta') . '.pdf';

        return response(self::output($surat), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $nama . '"',
        ]);
    }
}
