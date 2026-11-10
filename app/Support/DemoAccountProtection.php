<?php

namespace App\Support;

use App\Models\User;

final class DemoAccountProtection
{
    public const EMAILS = ['admin@kasorg.test', 'anggota@kasorg.test'];

    public const NIMS = ['24170001', '24170002'];

    public static function protects(User $user): bool
    {
        // Lindungi juga identitas yang hanya cocok sebagian, bukan sekadar ID.
        return in_array(mb_strtolower($user->email), self::EMAILS, true)
            || in_array($user->nim, self::NIMS, true);
    }
}
