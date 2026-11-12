<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/** Foto profil admin (menu foto di header admin). */
class ProfilAdminController extends Controller
{
    public function foto()
    {
        $path = Auth::guard('admin')->user()?->foto_profil;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function ubahFoto(Request $request)
    {
        $v = Validator::make($request->all(), [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image'    => 'Berkas harus berupa gambar (JPG/PNG).',
            'foto.mimes'    => 'Format foto harus JPG atau PNG.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
            'foto.uploaded' => 'Foto gagal diunggah (ukuran maksimal 2 MB).',
        ]);

        if ($v->fails()) {
            return back()->with('galat_foto', $v->errors()->first());
        }

        $admin = Auth::guard('admin')->user();
        $lama  = $admin->foto_profil;

        $admin->update(['foto_profil' => $request->file('foto')->store('admin/foto', 'local')]);

        if ($lama) {
            Storage::disk('local')->delete($lama);
        }

        return back()->with('sukses_foto', 'Foto profil berhasil diperbarui.');
    }
}
