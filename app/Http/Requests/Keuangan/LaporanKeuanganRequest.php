<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Support\Arr;

class LaporanKeuanganRequest extends RingkasanKasRequest
{
    public function rules(): array
    {
        return Arr::only(parent::rules(), ['periode_id', 'page']);
    }

    protected function getRedirectUrl(): string
    {
        return route($this->routeIs('admin.*') ? 'admin.laporan' : 'anggota.laporan');
    }
}
