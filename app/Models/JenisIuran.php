<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisIuran extends Model
{
    use HasFactory;

    protected $table = 'jenis_iuran';

    protected $fillable = [
        'nama',
        'nominal',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    public function tagihanIuran(): HasMany
    {
        return $this->hasMany(TagihanIuran::class);
    }
}
