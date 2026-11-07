<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTransaksi extends Model
{
    use HasFactory;

    public const PEMASUKAN = 'pemasukan';

    public const PENGELUARAN = 'pengeluaran';

    protected $table = 'kategori_transaksi';

    protected $fillable = [
        'nama',
        'jenis',
        'is_sistem',
    ];

    protected function casts(): array
    {
        return [
            'is_sistem' => 'boolean',
        ];
    }

    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class);
    }
}
