<?php

namespace App\Http\Controllers;

use App\Models\FormPenilaian;
use App\Models\Penilaian;
use App\Services\SinkronSuratKeterangan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Halaman publik (tanpa login) yang dibuka penilai lewat link form penilaian. */
class PenilaianPublikController extends Controller
{
    public function tampil(string $kode)
    {
        $form = FormPenilaian::where('kode', $kode)->with('kriteria')->firstOrFail();

        return view('penilaian.isi', [
            'form'   => $form,
            'peserta' => $form->dibuka()
                ? $form->pesertaBelumDinilai()->get(['id', 'nama', 'asal_sekolah', 'jurusan'])
                : collect(),
        ]);
    }

    public function kirim(Request $request, string $kode, SinkronSuratKeterangan $sinkron)
    {
        $form = FormPenilaian::where('kode', $kode)->with('kriteria')->firstOrFail();

        if (! $form->dibuka()) {
            return back()->withErrors(['form' => 'Form penilaian ini sudah ditutup.']);
        }

        $rules = [
            'data_magang_id'  => ['required', 'integer'],
            'nama_penilai'    => ['required', 'string', 'max:150'],
            'jabatan_penilai' => ['required', 'string', 'max:150'],
            'catatan'         => ['nullable', 'string', 'max:1000'],
        ];
        $pesan = [
            'data_magang_id.required'  => 'Pilih peserta yang dinilai.',
            'nama_penilai.required'    => 'Nama penilai wajib diisi.',
            'jabatan_penilai.required' => 'Jabatan penilai wajib diisi.',
        ];
        foreach ($form->kriteria as $k) {
            $rules["nilai.{$k->id}"]            = ['required', 'integer', 'between:0,100'];
            $pesan["nilai.{$k->id}.required"]   = "Nilai \"{$k->nama}\" wajib diisi.";
            $pesan["nilai.{$k->id}.integer"]    = "Nilai \"{$k->nama}\" harus berupa angka bulat.";
            $pesan["nilai.{$k->id}.between"]    = "Nilai \"{$k->nama}\" harus antara 0 dan 100.";
        }

        $data = $request->validate($rules, $pesan);

        // Peserta harus masih ada di daftar form ini (sesuai departemen & belum dinilai).
        $peserta = $form->pesertaBelumDinilai()->whereKey($data['data_magang_id'])->first();
        if (! $peserta) {
            return back()->withInput()->withErrors(['data_magang_id' => 'Peserta tidak ditemukan atau sudah dinilai di form ini.']);
        }

        $pasangan = $form->kriteria->map(fn ($k) => [(int) $data['nilai'][$k->id], $k->bobot]);
        $akhir    = Penilaian::hitung($pasangan);

        try {
            DB::transaction(function () use ($form, $peserta, $data, $akhir) {
                $penilaian = Penilaian::create([
                    'form_penilaian_id' => $form->id,
                    'data_magang_id'    => $peserta->id,
                    'nama_penilai'      => $data['nama_penilai'],
                    'jabatan_penilai'   => $data['jabatan_penilai'],
                    'nilai_akhir'       => $akhir,
                    'predikat'          => Penilaian::predikat($akhir),
                    'catatan'           => $data['catatan'] ?? null,
                ]);

                foreach ($form->kriteria as $k) {
                    $penilaian->nilai()->create([
                        'kriteria_id'   => $k->id,
                        'nama_kriteria' => $k->nama,
                        'bobot'         => $k->bobot,
                        'nilai'         => (int) $data['nilai'][$k->id],
                    ]);
                }
            });
        } catch (QueryException $e) {
            // Dua penilai mengirim untuk peserta yang sama hampir bersamaan (unique form + peserta).
            return back()->withInput()->withErrors(['data_magang_id' => 'Peserta ini baru saja dinilai di form yang sama.']);
        }

        // Nilai langsung masuk ke Surat Keterangan peserta (draft sampai diterbitkan HRD).
        $sinkron->jalankan($peserta);

        return redirect()->route('penilaian.isi', $kode)->with('terkirim', $peserta->nama);
    }
}
