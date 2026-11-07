<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_iuran', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_iuran');
    }
};
