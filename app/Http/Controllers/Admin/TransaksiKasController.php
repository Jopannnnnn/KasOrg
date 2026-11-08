<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\DeleteTransaksiKasRequest;
use App\Http\Requests\Keuangan\SaveTransaksiKasRequest;
use App\Models\TransaksiKas;
use App\Services\TransaksiManualService;

class TransaksiKasController extends Controller
{
    public function store(SaveTransaksiKasRequest $request, TransaksiManualService $service)
    {
        $service->save($request->user(), $request->validated());

        return to_route('admin.transaksi', ['periode_id' => $request->validated('periode_id')])->with('success', 'Transaksi manual berhasil ditambahkan. Saldo kas telah diperbarui.');
    }

    public function update(SaveTransaksiKasRequest $request, TransaksiKas $transaksi, TransaksiManualService $service)
    {
        $service->save($request->user(), $request->validated(), $transaksi);

        return to_route('admin.transaksi', ['periode_id' => $request->validated('periode_id')])->with('success', 'Transaksi manual berhasil diubah. Saldo kas telah diperbarui.');
    }

    public function destroy(DeleteTransaksiKasRequest $request, TransaksiKas $transaksi, TransaksiManualService $service)
    {
        $periodId = $service->delete($request->user(), $transaksi);

        return to_route('admin.transaksi', ['periode_id' => $periodId])->with('success', 'Transaksi manual berhasil diarsipkan dan tidak lagi dihitung dalam saldo.');
    }
}
