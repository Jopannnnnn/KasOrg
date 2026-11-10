<?php

namespace App\Http\Requests\Anggota;

class UpdateAnggotaRequest extends SaveAnggotaRequest
{
    public function rules(): array
    {
        return [
            ...$this->identityRules(),
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
        ];
    }
}
