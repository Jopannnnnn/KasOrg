<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiKas extends Model
{
    use HasFactory, SoftDeletes;

    public const PEMASUKAN = 'pemasukan';

    public const PENGELUARAN = 'pengeluaran';

    public const SUMBER_MANUAL = 'manual';

    public const SUMBER_PEMBAYARAN_IURAN = 'pembayaran_iuran';

    protected $table = 'transaksi_kas';

    protected $fillable = [
        'tanggal',
        'jenis',
        'kategori_transaksi_id',
        'tagihan_iuran_id',
        'pembayaran_iuran_id',
        'keterangan',
        'nominal',
        'sumber',
        'dibuat_oleh',
        'diubah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function kategoriTransaksi(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class);
    }

    public function pembayaranIuran(): BelongsTo
    {
        return $this->belongsTo(PembayaranIuran::class);
    }

    public function tagihanIuran(): BelongsTo
    {
        return $this->belongsTo(TagihanIuran::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
