<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\PermintaanResetPassword;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Akun Peserta: untuk sekarang fungsinya reset kata sandi (peserta lupa sandi).
 * Reset hanya mengembalikan sandi ke sandi awal + wajib ganti sandi. Riwayat absen & tugas TIDAK disentuh.
 */
class AkunPesertaController extends Controller
{
    public function index(Request $r)
    {
        $menunggu = PermintaanResetPassword::with('user.departemen')
            ->where('status', 'menunggu')->orderBy('created_at')->get();

        $riwayat = PermintaanResetPassword::where('status', '!=', 'menunggu')
            ->orderByDesc('ditangani_at')->limit(15)->get();

        $q = User::with('departemen')->where('peran', 'peserta')->orderBy('name')
            ->when($r->filled('q'), function ($x) use ($r) {
                $kata = '%' . $r->query('q') . '%';
                $x->where(fn ($w) => $w->where('name', 'like', $kata)->orWhere('nis_nim', 'like', $kata));
            })
            ->when($r->filled('departemen_id'), fn ($x) => $x->where('departemen_id', $r->query('departemen_id')))
            ->when($r->query('sandi') === 'awal', fn ($x) => $x->where('wajib_ganti_password', true))
            ->when($r->query('sandi') === 'diganti', fn ($x) => $x->where('wajib_ganti_password', false));

        return view('admin.akun.index', [
            'menunggu' => $menunggu,
            'riwayat' => $riwayat,
            'peserta' => $q->paginate(50)->withQueryString(),
            'departemen' => Departemen::orderBy('nama')->get(),
        ]);
    }

    /** Reset langsung dari daftar akun (tanpa permintaan). Permintaan yang masih menunggu untuk akun ini ikut ditutup. */
    public function reset(User $peserta)
    {
        abort_unless($peserta->peran === 'peserta', 404);

        $this->kembalikanSandiAwal($peserta);

        PermintaanResetPassword::where('user_id', $peserta->id)->where('status', 'menunggu')
            ->update($this->penanda('selesai'));

        return back()->with('success', "Sandi {$peserta->name} direset. Peserta masuk dengan NIM dan sandi awal, lalu wajib membuat sandi baru.");
    }

    /** Reset dari permintaan "Lupa kata sandi". */
    public function selesaikan(PermintaanResetPassword $permintaan)
    {
        abort_unless($permintaan->status === 'menunggu', 404);

        $user = $permintaan->user;

        if (! $user || $user->peran !== 'peserta') {
            return back()->with('error', 'Akun peserta untuk permintaan ini tidak ditemukan (mungkin sudah dihapus).');
        }

        $this->kembalikanSandiAwal($user);
        $permintaan->update($this->penanda('selesai'));

        // Permintaan ganda dari peserta yang sama ikut selesai.
        PermintaanResetPassword::where('user_id', $user->id)->where('status', 'menunggu')
            ->update($this->penanda('selesai'));

        return back()->with('success', "Sandi {$user->name} direset. Peserta akan melihat pemberitahuan saat masuk dengan sandi awal.");
    }

    public function tolak(Request $r, PermintaanResetPassword $permintaan)
    {
        abort_unless($permintaan->status === 'menunggu', 404);

        $d = $r->validate(['catatan_admin' => ['nullable', 'string', 'max:255']]);

        $permintaan->update($this->penanda('ditolak') + ['catatan_admin' => $d['catatan_admin'] ?? null]);

        return back()->with('success', "Permintaan dari {$permintaan->nama} ditolak.");
    }

    private function kembalikanSandiAwal(User $user): void
    {
        $user->forceFill([
            'password' => User::PASSWORD_DEFAULT, // di-hash otomatis oleh cast 'hashed'
            'wajib_ganti_password' => true,
            'remember_token' => null,             // sesi "ingat saya" lama ikut tidak berlaku
        ])->save();
    }

    private function penanda(string $status): array
    {
        $admin = auth('admin')->user();

        return [
            'status' => $status,
            'ditangani_oleh' => $admin->nama ?? $admin->email ?? 'Admin',
            'ditangani_at' => now(),
        ];
    }
}