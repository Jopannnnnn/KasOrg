<?php

namespace App\Http\Requests\Anggota;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateStatusAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('updateStatus', $this->route('anggota'));
    }

    public function rules(): array
    {
        return ['status_akun' => ['required', Rule::in([User::STATUS_AKTIF, User::STATUS_NONAKTIF])]];
    }

    public function messages(): array
    {
        return [
            'status_akun.required' => 'Status akun wajib dipilih.',
            'status_akun.in' => 'Status akun harus aktif atau nonaktif.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.anggota');
    }
}
