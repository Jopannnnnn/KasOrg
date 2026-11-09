<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KategoriTransaksiRequest;
use App\Http\Requests\Api\RingkasanRequest;
use App\Http\Responses\ApiResponse;
use App\Models\KategoriTransaksi;
use App\Services\RingkasanKasService;
use Illuminate\Http\JsonResponse;

class KasController extends Controller
{
    public function categories(KategoriTransaksiRequest $request): JsonResponse
    {
        $query = KategoriTransaksi::query()->select(['id', 'nama', 'jenis'])->orderBy('jenis')->orderBy('nama')->orderBy('id');
        if ($request->validated('jenis')) {
            $query->where('jenis', $request->validated('jenis'));
        }

        return ApiResponse::success($query->get()->toArray(), 'Kategori transaksi berhasil diambil.');
    }

    public function summary(RingkasanRequest $request, RingkasanKasService $service): JsonResponse
    {
        $scope = $service->scope($request->validated());
        $summary = $service->summary($scope);

        return ApiResponse::success([
            'periode' => [
                'id' => $scope['selected'], 'nama' => $scope['label'], 'tanggal_mulai' => $scope['start'],
                'tanggal_selesai' => $scope['end'], 'periode_terakhir_ditutup' => $scope['closed'],
            ],
            'mata_uang' => 'IDR', 'saldo_awal' => $summary['opening'],
            'pemasukan' => $summary['income'], 'pengeluaran' => $summary['expense'], 'saldo_akhir' => $summary['closing'],
            'jumlah_transaksi' => $summary['count'], 'saldo_akhir_snapshot' => $scope['snapshot'],
            'snapshot_berbeda' => $summary['snapshotMismatch'],
            'grafik_bulanan' => array_map(fn ($bar) => [
                'bulan' => $bar['month'], 'pemasukan' => $bar['income'], 'pengeluaran' => $bar['expense'],
            ], $summary['bars']),
            'alokasi_pengeluaran' => array_map(fn ($allocation) => [
                'kategori' => $allocation['name'], 'nominal' => $allocation['amount'], 'persentase' => $allocation['percentage'],
            ], $summary['allocations']),
        ], 'Ringkasan kas berhasil diambil.');
    }
}
