<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'admin';

    public const ANGGOTA = 'anggota';

    protected $fillable = [
        'kode',
        'nama',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
