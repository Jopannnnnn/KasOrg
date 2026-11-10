<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan KasOrg</title>
    <style>
        @page { margin: 28px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 20px; color: #234578; margin-bottom: 4px; }
        h2 { font-size: 13px; margin-top: 24px; }
        .muted { color: #64748b; }
        .summary, .transactions { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th { background: #234578; color: white; text-align: left; padding: 8px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 7px; vertical-align: top; overflow-wrap: anywhere; }
        .number { text-align: right; white-space: nowrap; }
        .summary td { background: #f0f4fa; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .description { width: 28%; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>KasOrg</h1>
    <p>Sistem Kas Organisasi Mahasiswa — Laporan Keuangan</p>
    <p><strong>Periode:</strong> {{ $scope['label'] }}@if ($scope['start']) ({{ \Carbon\CarbonImmutable::parse($scope['start'])->format('d/m/Y') }}–{{ \Carbon\CarbonImmutable::parse($scope['end'])->format('d/m/Y') }})@endif</p>
    <p class="muted">Tanggal cetak: {{ $printedAt }}. Hak akses laporan: {{ ucfirst($role) }}.</p>
    <table class="summary">
        <thead><tr><th>Saldo awal</th><th>Total pemasukan</th><th>Total pengeluaran</th><th>Saldo akhir</th></tr></thead>
        <tbody><tr><td>{{ \App\Services\RingkasanKasService::rupiah($summary['opening']) }}</td><td>{{ \App\Services\RingkasanKasService::rupiah($summary['income']) }}</td><td>{{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</td><td>{{ \App\Services\RingkasanKasService::rupiah($summary['closing']) }}</td></tr></tbody>
    </table>
    <p>Saldo akhir = saldo awal + pemasukan − pengeluaran. Tagihan belum dibayar serta pengajuan menunggu/ditolak tidak menambah kas.</p>
    @if ($scope['selected'] === 'semua')<p class="muted">Saldo awal diambil dari periode paling awal, tidak dijumlahkan berulang.</p>@endif
    @if ($summary['snapshotMismatch'])<p>Perhatian: snapshot penutupan berbeda dari perhitungan transaksi sah. Laporan mengikuti rumus di atas.</p>@endif
    <h2>Rincian transaksi kas ({{ $summary['count'] }} transaksi)</h2>
    <table class="transactions">
        <thead><tr><th>No.</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th class="number">Nominal</th></tr></thead>
        <tbody>
            @forelse ($transactions as $item)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $item['date'] }}</td><td>{{ $item['type'] }}</td><td>{{ $item['category'] }}</td><td class="description">{{ $item['description'] }}</td><td class="number">{{ $item['amount'] }}</td></tr>
            @empty
                <tr><td colspan="6">Belum ada transaksi kas sah pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="muted">Keterangan Anggota mengikuti proyeksi privasi laporan. Bukti transfer dan identitas pribadi anggota lain tidak disertakan.</p>
</body>
</html>
