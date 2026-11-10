<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Foundation\Http\FormRequest;

abstract class KeuanganRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk pilihan ini.',
            'accepted_if' => 'Konfirmasi penerimaan uang atau pencocokan mutasi wajib dicentang sebelum menyetujui.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'in' => 'Pilihan :attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => ':attribute sudah digunakan.',
            'max' => ':attribute melebihi batas yang diizinkan (:max).',
            'min' => ':attribute minimal :min.',
            'gt' => ':attribute harus lebih besar dari :value.',
            'decimal' => ':attribute hanya boleh memiliki maksimal dua angka desimal.',
            'regex' => 'Format :attribute tidak valid.',
            'date_format' => ':attribute harus berupa tanggal yang valid (tahun-bulan-hari).',
            'before_or_equal' => ':attribute tidak boleh melewati hari ini.',
            'image' => ':attribute harus berupa gambar JPG, PNG, atau WebP.',
            'dimensions' => ':attribute harus memiliki ukuran gambar valid, maksimal 10000 x 10000 piksel.',
            'mimes' => ':attribute harus berupa gambar JPG, PNG, atau WebP.',
            'mimetypes' => 'Isi :attribute harus berupa gambar JPG, PNG, atau WebP.',
            'file' => ':attribute harus berupa file.',
            'uploaded' => 'Bukti gagal diunggah. Periksa ukuran file dan batas unggahan server.',
            'prohibited' => ':attribute ditetapkan oleh sistem dan tidak boleh dikirim.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'Anggota', 'jenis_iuran_id' => 'Jenis iuran', 'periode_id' => 'Periode',
            'jatuh_tempo' => 'Jatuh tempo', 'nama' => 'Nama jenis iuran', 'nominal' => 'Nominal',
            'deskripsi' => 'Deskripsi', 'metode' => 'Metode pembayaran', 'tanggal_bayar' => 'Tanggal pembayaran',
            'bukti' => 'Bukti transfer', 'catatan' => 'Catatan', 'keputusan' => 'Keputusan',
            'alasan_penolakan' => 'Alasan penolakan', 'q' => 'Pencarian', 'status' => 'Status', 'page' => 'Halaman',
        ];
    }
}
