<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keuangan\IndexKeuanganRequest;
use App\Http\Requests\Keuangan\StoreJenisIuranRequest;
use App\Http\Requests\Keuangan\StoreTagihanRequest;
use App\Models\JenisIuran;
use App\Models\Periode;
use App\Models\TagihanIuran;
use App\Models\User;
use App\Services\IuranService;
use App\Services\JenisIuranService;

class IuranController extends Controller
{
    public function index(IndexKeuanganRequest $request)
    {
        $filters = $request->validated();
        $query = TagihanIuran::with(['anggota', 'jenisIuran', 'periode', 'pembayaranIuran' => fn ($q) => $q->latest('id')]);
        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }
        $stats = [
            'belum_lunas' => (clone $query)->where('status', 'belum_lunas')->sum('nominal'),
            'menunggu' => (clone $query)->whereHas('pembayaranIuran', fn ($q) => $q->where('status', 'menunggu'))->count(),
            'lunas' => (clone $query)->where('status', 'lunas')->count(),
        ];
        if (($filters['q'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(function ($q) use ($pattern) {
                $q->whereHas('jenisIuran', fn ($q) => $q->whereRaw("nama LIKE ? ESCAPE '!'", [$pattern]))
                    ->orWhereHas('anggota', fn ($q) => $q->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])->orWhereRaw("nim LIKE ? ESCAPE '!'", [$pattern]))
                    ->orWhereHas('periode', fn ($q) => $q->whereRaw("tahun LIKE ? ESCAPE '!'", [$pattern]));
            });
        }
        if (in_array($filters['status'] ?? '', ['lunas', 'belum_lunas'], true)) {
            $query->where('status', $filters['status']);
        } elseif (in_array($filters['status'] ?? '', ['menunggu', 'ditolak'], true)) {
            // Status pengajuan TERAKHIR, bukan histori penolakan yang sudah diajukan ulang.
            $query->where('status', 'belum_lunas')->whereHas('pembayaranIuran', function ($q) use ($filters) {
                $q->where('status', $filters['status'])->whereRaw('pembayaran_iuran.id = (select max(p.id) from pembayaran_iuran p where p.tagihan_iuran_id = tagihan_iuran.id)');
            });
        }
        $bills = $query->latest('id')->paginate(10)->withQueryString();
        $data = compact('bills', 'stats', 'filters');
        if ($request->user()->isAdmin()) {
            $data['members'] = User::where('status_akun', 'aktif')->whereHas('role', fn ($q) => $q->where('kode', 'anggota'))->orderBy('name')->get();
            $data['types'] = JenisIuran::where('aktif', true)->orderBy('nama')->get();
            $data['periods'] = Periode::where('status', 'terbuka')->orderByDesc('tahun')->orderByDesc('bulan')->get();
        }

        return view($request->user()->isAdmin() ? 'admin.iuran.index' : 'anggota.tagihan.index', $data);
    }

    public function store(StoreTagihanRequest $request, IuranService $service)
    {
        $service->createBill($request->user(), $request->validated());

        return to_route('admin.iuran')->with('success', 'Tagihan berhasil dibuat. Belum ada pemasukan kas.');
    }

    public function storeJenis(StoreJenisIuranRequest $request, JenisIuranService $service)
    {
        $service->save($request->user(), $request->validated());

        return to_route('admin.iuran.jenis.index', $request->listingParameters())
            ->with('success', 'Jenis iuran berhasil ditambahkan.');
    }
}
