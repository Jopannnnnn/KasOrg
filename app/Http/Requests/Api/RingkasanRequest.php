<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Keuangan\KeuanganRequest;
use Illuminate\Validation\Rule;

class RingkasanRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return true; // Auth, status aktif dan role wajib pada route.
    }

    public function rules(): array
    {
        return ['periode_id' => ['nullable', Rule::when($this->input('periode_id') !== 'semua', ['integer', 'min:1', 'exists:periode,id'])]];
    }
}
