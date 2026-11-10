<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keuangan\RingkasanKasRequest;
use App\Models\KategoriTransaksi;
use App\Models\PembayaranIuran;
use App\Models\Periode;
use App\Models\TagihanIuran;
use App\Models\User;
use App\Services\RingkasanKasService;
use Illuminate\Support\Facades\Gate;

class RingkasanKasController extends Controller
{
    public function dashboard(RingkasanKasRequest $request, RingkasanKasService $service)
    {
        $scope = $service->scope($request->validated());
        $summary = $service->summary($scope);
        $isAdmin = $request->user()->isAdmin();
        $billQuery = TagihanIuran::whereIn('periode_id', $scope['ids']);
        if (! $isAdmin) {
            $billQuery->where('user_id', $request->user()->id);
        }
        $billStats = [
            'total' => (clone $billQuery)->count(),
            'unpaid' => (clone $billQuery)->where('status', 'belum_lunas')->count(),
        ];
        $paymentQuery = PembayaranIuran::whereHas('tagihanIuran', function ($q) use ($scope, $isAdmin, $request) {
            $q->whereIn('periode_id', $scope['ids']);
            if (! $isAdmin) {
                $q->where('user_id', $request->user()->id);
            }
        });
        $billStats['waiting'] = (clone $paymentQuery)->where('status', 'menunggu')->count();
        $personalPayments = $isAdmin ? collect() : (clone $paymentQuery)->with('tagihanIuran.jenisIuran')->latest('id')->limit(10)->get();
        $personalBills = $isAdmin ? collect() : (clone $billQuery)->with('jenisIuran')->latest('id')->limit(10)->get();
        $activeMembers = $isAdmin ? User::where('status_akun', 'aktif')->whereHas('role', fn ($q) => $q->where('kode', 'anggota'))->count() : null;
        $transactions = $service->rows($service->transactions($scope)->with('kategoriTransaksi')->orderByDesc('tanggal')->orderByDesc('id')->limit(5)->get(), ! $isAdmin);

        return view($isAdmin ? 'admin.dashboard' : 'anggota.dashboard', compact('scope', 'summary', 'billStats', 'personalBills', 'personalPayments', 'activeMembers', 'transactions'));
    }

    public function transparency(RingkasanKasRequest $request, RingkasanKasService $service)
    {
        $filters = $request->validated();
        $scope = $service->scope($filters);
        $summary = $service->summary($scope);
        $query = $service->transactions($scope)->with('kategoriTransaksi')->orderByDesc('tanggal')->orderByDesc('id');
        if ($filters['jenis'] ?? null) {
            $query->where('jenis', $filters['jenis']);
        }
        $transactions = $query->paginate(10)->withQueryString();
        // Transparansi hanya menyediakan proyeksi aman, termasuk saat dibuka Admin.
        $transactions->setCollection($service->rows($transactions->getCollection(), true));
        $role = $request->user()->role->kode;

        return view('transparansi.index', compact('scope', 'summary', 'transactions', 'filters', 'role'));
    }

    public function transactions(RingkasanKasRequest $request, RingkasanKasService $service)
    {
        $filters = $request->validated();
        $scope = $service->scope($filters);
        $summary = $service->summary($scope);
        $query = $service->transactions($scope)->with('kategoriTransaksi')->orderByDesc('tanggal')->orderByDesc('id');
        if ($filters['jenis'] ?? null) {
            $query->where('jenis', $filters['jenis']);
        }
        if (($filters['q'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(function ($query) use ($pattern) {
                $query->whereRaw("keterangan LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereHas('kategoriTransaksi', fn ($q) => $q->whereRaw("nama LIKE ? ESCAPE '!'", [$pattern]));
            });
        }
        $transactions = $query->paginate(10)->withQueryString();
        $records = $transactions->getCollection();
        $rows = $service->rows($records, false);
        $transactions->setCollection($records->values()->map(function ($record, $index) use ($rows, $scope) {
            $matches = $scope['periods']->filter(fn ($p) => $p->tanggal_mulai->lte($record->tanggal) && $p->tanggal_selesai->gte($record->tanggal));
            $manageable = Gate::allows('update', $record) && $matches->count() === 1 && $matches->first()->status === Periode::TERBUKA;

            return $rows[$index] + ['id' => $record->id, 'manageable' => $manageable, 'source' => $record->sumber, 'record' => $record, 'period_id' => $matches->count() === 1 ? $matches->first()->id : null];
        }));
        $categories = KategoriTransaksi::orderBy('jenis')->orderBy('nama')->get();
        $openPeriods = $scope['periods']->where('status', Periode::TERBUKA)->values();

        return view('admin.transaksi.index', compact('scope', 'summary', 'transactions', 'filters', 'categories', 'openPeriods'));
    }
}
