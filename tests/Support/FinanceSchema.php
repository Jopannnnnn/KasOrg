<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceSchema
{
    // Fixture SQLite RAM: tidak menjalankan migration aplikasi atau DDL di MySQL.
    public static function create(): void
    {
        if (! app()->environment('testing') || DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Fixture schema hanya diizinkan di SQLite RAM testing.');
        }
        Schema::create('kategori_transaksi', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 60);
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->boolean('is_sistem')->default(false);
            $table->timestamps();

            $table->unique(['nama', 'jenis']);
            $table->unique(['id', 'jenis']);
        });
        Schema::create('jenis_iuran', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('periode', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_akhir_snapshot', 15, 2)->nullable();
            $table->enum('status', ['terbuka', 'ditutup'])->default('terbuka')->index();
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'bulan']);
            $table->unique(['tanggal_mulai', 'tanggal_selesai'], 'periode_rentang_unique');
        });
        Schema::create('tagihan_iuran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('jenis_iuran_id')->constrained('jenis_iuran')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas')->index();
            $table->timestamp('lunas_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'jenis_iuran_id', 'periode_id'],
                'tagihan_user_jenis_periode_unique'
            );
        });
        Schema::create('pembayaran_iuran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tagihan_iuran_id')->constrained('tagihan_iuran')->restrictOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->enum('metode', ['tunai', 'transfer']);
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->date('tanggal_bayar');
            $table->string('bukti_path', 255)->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                ->default('menunggu')
                ->index();
            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->string('alasan_penolakan', 255)->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->index(['tagihan_iuran_id', 'status']);
        });
        Schema::create('transaksi_kas', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal')->index();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->foreignId('kategori_transaksi_id');
            $table->foreignId('tagihan_iuran_id')
                ->nullable()
                ->unique()
                ->constrained('tagihan_iuran')
                ->restrictOnDelete();
            $table->foreignId('pembayaran_iuran_id')
                ->nullable()
                ->unique()
                ->constrained('pembayaran_iuran')
                ->restrictOnDelete();
            $table->string('keterangan', 255);
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->enum('sumber', ['manual', 'pembayaran_iuran'])->default('manual');
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['kategori_transaksi_id', 'jenis'])
                ->references(['id', 'jenis'])
                ->on('kategori_transaksi')
                ->restrictOnDelete();
            $table->index(['jenis', 'tanggal']);
        });
    }
}
