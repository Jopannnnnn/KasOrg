<?php

namespace App\Http\Requests\Anggota;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('viewAny', User::class);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([User::STATUS_AKTIF, User::STATUS_NONAKTIF])],
            'per_page' => ['sometimes', 'integer', Rule::in([5, 10, 25, 50])],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.string' => 'Kata pencarian harus berupa teks.',
            'q.max' => 'Kata pencarian maksimal 100 karakter.',
            'status.in' => 'Filter status akun tidak valid.',
            'per_page.integer' => 'Jumlah data per halaman harus berupa angka.',
            'per_page.in' => 'Jumlah data per halaman harus 5, 10, 25, atau 50.',
            'page.integer' => 'Nomor halaman harus berupa angka.',
            'page.min' => 'Nomor halaman minimal 1.',
            'page.max' => 'Nomor halaman terlalu besar.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.anggota');
    }
}
