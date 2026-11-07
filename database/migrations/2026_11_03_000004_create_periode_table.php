<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('periode');
    }
};
