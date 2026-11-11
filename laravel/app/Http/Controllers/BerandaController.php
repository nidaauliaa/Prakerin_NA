<?php

namespace App\Http\Controllers;

use App\Models\Lamaran;
use App\Models\Lowongan;

class BerandaController extends Controller
{
    public function index()
    {
        $lowongan = Lowongan::dibuka()->orderBy('id')->get();

        // Rentang tanggal yang sudah terisi, 1 query untuk semua posisi
        $terisi = Lamaran::whereIn('lowongan_id', $lowongan->pluck('id'))
            ->whereIn('status', Lamaran::STATUS_MENGISI_KUOTA)
            ->whereDate('tgl_selesai', '>=', today())
            ->get(['lowongan_id', 'tgl_mulai', 'tgl_selesai'])
            ->groupBy('lowongan_id');

        // Bentuknya sama persis dengan array jobList di prototype
        $jobs = $lowongan->map(fn (Lowongan $l) => [
            'id'            => $l->id,
            'title'         => $l->judul,
            'category'      => $l->tipe_magang === 'kuliah' ? 'mahasiswa' : 'smk',
            'categoryLabel' => $l->tipe_magang === 'kuliah' ? 'Mahasiswa' : 'Anak SMK',
            'maxQuota'      => $l->kuota,
            'major'         => $l->jurusan_diterima ?? '-',
            'description'   => $l->deskripsi ?? '',
            'bookedRanges'  => ($terisi[$l->id] ?? collect())->map(fn ($r) => [
                'start' => $r->tgl_mulai->toDateString(),
                'end'   => $r->tgl_selesai->toDateString(),
            ])->values(),
        ])->values();

        return view('beranda', compact('jobs'));
    }
}