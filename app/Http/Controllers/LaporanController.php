<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keuangan\LaporanKeuanganRequest;
use App\Services\RingkasanKasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    public function __invoke(LaporanKeuanganRequest $request, RingkasanKasService $service)
    {
        return view('laporan.index', $this->data($request, $service, false));
    }

    public function pdf(LaporanKeuanganRequest $request, RingkasanKasService $service)
    {
        $data = $this->data($request, $service, true);
        $data['printedAt'] = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB';
        $period = $data['scope']['selected'] ?? 'kosong';
        $filename = 'laporan-kasorg-'.$period.'-'.now()->format('Ymd-His').'.pdf';

        return Pdf::setOptions([
            'isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false,
            'allowedProtocols' => ['file://'], 'chroot' => base_path('vendor/dompdf/dompdf/lib/fonts'),
            'defaultFont' => 'DejaVu Sans', 'isFontSubsettingEnabled' => true,
        ], true)->loadView('laporan.pdf', $data)->setPaper('a4', 'portrait')->download($filename)
            ->header('Cache-Control', 'private, no-store, max-age=0')->header('Pragma', 'no-cache')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function data(LaporanKeuanganRequest $request, RingkasanKasService $service, bool $forPdf): array
    {
        // Satu snapshot baca untuk angka/grafik/rincian. Tidak menulis data kas.
        return DB::transaction(function () use ($request, $service, $forPdf) {
            $scope = $service->scope($request->validated());
            $summary = $service->summary($scope);
            $query = $service->transactions($scope)->with('kategoriTransaksi')->orderByDesc('tanggal')->orderByDesc('id');
            if ($forPdf) {
                // Jangan memotong rincian secara diam-diam atau menghabiskan memori tanpa batas.
                if ($query->count() > 2000) {
                    throw ValidationException::withMessages(['periode_id' => 'Laporan PDF maksimal 2.000 transaksi. Pilih periode yang lebih sempit.'])
                        ->redirectTo(route($request->routeIs('admin.*') ? 'admin.laporan' : 'anggota.laporan', ['periode_id' => $scope['selected']]));
                }
                $transactions = $service->rows($query->get(), ! $request->user()->isAdmin());
            } else {
                $transactions = $query->paginate(10)->appends($request->safe()->only(['periode_id']));
                $transactions->setCollection($service->rows($transactions->getCollection(), ! $request->user()->isAdmin()));
            }
            $role = $request->user()->role->kode;

            return compact('scope', 'summary', 'transactions', 'role');
        }, 3);
    }
}
