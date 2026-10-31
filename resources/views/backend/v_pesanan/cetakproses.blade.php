@php
    $totalPesanan = $cetak->sum(fn ($row) => (float) $row->total_harga + (float) $row->biaya_ongkir);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $subJudul }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #30323a; margin: 24px; }
        h2 { margin: 0 0 4px; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #999; padding: 6px 8px; vertical-align: top; }
        th { background: #eeeeee; text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        tfoot th, tfoot td { font-weight: bold; }
        .summary { margin-top: 14px; }
    </style>
</head>
<body>
    <h2>{{ $judul }} — {{ $subJudul }}</h2>
    <p>Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
    <p>Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>ID Order</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cetak as $row)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>#{{ $row->id }}</td>
                <td>{{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}</td>
                <td>
                    {{ $row->customer->user->nama ?? '-' }}<br>
                    <small>{{ $row->customer->user->email ?? '-' }}</small>
                </td>
                <td>{{ $row->status_label }}</td>
                <td class="text-right">Rp. {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada pesanan proses pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total Nilai Pesanan Proses</th>
                <th class="text-right">Rp. {{ number_format($totalPesanan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <p>Jumlah pesanan: {{ $cetak->count() }}</p>
        <p>Total nilai pesanan: Rp. {{ number_format($totalPesanan, 0, ',', '.') }}</p>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
