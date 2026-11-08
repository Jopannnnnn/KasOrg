<?php

namespace App\Http\Requests\Keuangan;

class IndexKeuanganRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAktif();
    }

    public function rules(): array
    {
        $statuses = $this->routeIs('admin.verifikasi') ? 'menunggu,disetujui,ditolak' : 'belum_lunas,lunas,menunggu,ditolak';

        return ['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:'.$statuses], 'page' => ['nullable', 'integer', 'min:1', 'max:100000']];
    }

    protected function getRedirectUrl(): string
    {
        return route($this->route()->getName());
    }
}
