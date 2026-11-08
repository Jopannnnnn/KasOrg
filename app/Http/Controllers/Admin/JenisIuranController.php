<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\ManageJenisIuranRequest;
use App\Http\Requests\Keuangan\UpdateJenisIuranRequest;
use App\Models\JenisIuran;
use App\Services\JenisIuranService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JenisIuranController extends Controller
{
    public function index(ManageJenisIuranRequest $request)
    {
        $filters = $request->validated();
        $query = JenisIuran::withCount('tagihanIuran');
        if (($filters['q'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(function ($query) use ($pattern) {
                $query->whereRaw("nama LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("deskripsi LIKE ? ESCAPE '!'", [$pattern]);
            });
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('aktif', $filters['status'] === 'aktif');
        }
        $types = $query->orderBy('nama')->orderBy('id')->paginate(10)->withQueryString();

        return view('admin.iuran.jenis.index', compact('types', 'filters'));
    }

    public function update(UpdateJenisIuranRequest $request, JenisIuran $jenisIuran, JenisIuranService $service)
    {
        $service->save($request->user(), $request->validated(), $jenisIuran);

        return to_route('admin.iuran.jenis.index', $request->listingParameters())->with('success', 'Jenis iuran berhasil diubah. Nominal tagihan lama tetap.');
    }

    public function status(ManageJenisIuranRequest $request, JenisIuran $jenisIuran, JenisIuranService $service)
    {
        $service->setStatus($request->user(), $jenisIuran, $request->boolean('aktif'));

        return to_route('admin.iuran.jenis.index', $request->listingParameters())->with('success', 'Status jenis iuran berhasil diperbarui. Riwayat tetap tersimpan.');
    }

    public function destroy(ManageJenisIuranRequest $request, JenisIuran $jenisIuran, JenisIuranService $service)
    {
        try {
            $service->delete($request->user(), $jenisIuran);
        } catch (ModelNotFoundException $exception) {
            // Row dapat hilang setelah binding, ketika request lain selesai lebih dulu.
            if ($exception->getModel() !== JenisIuran::class) {
                throw $exception;
            }

            return $this->missing($request);
        }

        return to_route('admin.iuran.jenis.index', $request->listingParameters(), 303)->with('success', 'Jenis iuran yang belum digunakan berhasil dihapus.');
    }

    public function missing(Request $request)
    {
        // Missing binding terjadi sebelum Form Request: tetap lakukan authorization.
        Gate::authorize('viewAny', JenisIuran::class);

        return to_route('admin.iuran.jenis.index', [], 303)->withErrors([
            'jenis_iuran' => 'Jenis iuran tidak ditemukan atau sudah dihapus. Daftar telah dimuat ulang; tidak ada penghapusan tambahan.',
        ]);
    }
}
