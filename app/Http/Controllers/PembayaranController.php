<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keuangan\IndexKeuanganRequest;
use App\Http\Requests\Keuangan\StorePembayaranRequest;
use App\Http\Requests\Keuangan\VerifyPembayaranRequest;
use App\Models\PembayaranIuran;
use App\Models\TagihanIuran;
use App\Services\IuranService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function store(StorePembayaranRequest $request, TagihanIuran $tagihan, IuranService $service)
    {
        $path = null;
        try {
            if ($request->validated('metode') === 'transfer') {
                $path = $request->file('bukti')->store('transfer', 'bukti');
            }
            $service->submit($request->user(), $tagihan, $request->validated(), $path);
        } catch (\Throwable $exception) {
            if ($path !== null) {
                Storage::disk('bukti')->delete($path);
            }
            throw $exception;
        }

        return to_route('anggota.tagihan')->with('success', 'Pengajuan tersimpan dan menunggu verifikasi Admin.');
    }

    public function index(IndexKeuanganRequest $request)
    {
        $filters = $request->validated();
        $query = PembayaranIuran::with(['tagihanIuran.anggota', 'tagihanIuran.jenisIuran', 'tagihanIuran.periode', 'verifikator', 'transaksiKas']);
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }
        if (($filters['q'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(function ($q) use ($pattern) {
                $q->whereHas('tagihanIuran.anggota', fn ($q) => $q->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])->orWhereRaw("nim LIKE ? ESCAPE '!'", [$pattern]))
                    ->orWhereHas('tagihanIuran.jenisIuran', fn ($q) => $q->whereRaw("nama LIKE ? ESCAPE '!'", [$pattern]));
            });
        }
        $payments = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.pembayaran.verifikasi', compact('payments', 'filters'));
    }

    public function verify(VerifyPembayaranRequest $request, PembayaranIuran $pembayaran, IuranService $service)
    {
        $service->verify($request->user(), $pembayaran, $request->validated());

        return to_route('admin.verifikasi')->with('success', $request->validated('keputusan') === 'disetujui'
            ? 'Pembayaran disetujui, tagihan lunas, dan satu pemasukan kas tercatat.'
            : 'Pembayaran ditolak. Alasan tersimpan dan anggota dapat mengajukan ulang.');
    }

    public function proof(PembayaranIuran $pembayaran)
    {
        Gate::authorize('view', $pembayaran);
        $disk = Storage::disk('bukti');
        abort_unless($pembayaran->bukti_path && $disk->exists($pembayaran->bukti_path), 404);

        return $disk->response($pembayaran->bukti_path, 'bukti-pembayaran.'.pathinfo($pembayaran->bukti_path, PATHINFO_EXTENSION), [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
