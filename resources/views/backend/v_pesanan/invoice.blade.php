<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 24px;
        }

        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            font-size: 20px;
        }

        .header p {
            margin: 2px 0;
        }

        .text-right {
            text-align: right;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }

        table th,
        table td {
            border: 1px solid #bbb;
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            background: #f2f2f2;
        }

        table.info td {
            border: none;
            padding: 2px 0;
        }

        .totals td {
            text-align: right;
        }

        .totals .label {
            text-align: right;
        }

        .footer {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
        }

        .sign {
            width: 200px;
            text-align: center;
        }

        .sign .space {
            height: 60px;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <div>
                <h2>Toko Online</h2>
                <p>Web Programming III - Studi Kasus Toko Online</p>
                <p>Jl. Contoh No. 123, Jakarta</p>
            </div>
            <div class="text-right">
                <h3>INVOICE</h3>
                <p>No. {{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p>{{ ($order->tanggal ?? $order->created_at)->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <table class="info">
            <tr>
                <td width="50%">
                    <strong>Ditagihkan kepada:</strong><br>
                    {{ optional(optional($order->customer)->user)->nama ?? '-' }}<br>
                    {{ optional(optional($order->customer)->user)->email ?? '-' }}<br>
                    {{ optional(optional($order->customer)->user)->hp ?? '-' }}<br>
                    {{ $order->alamat ?? '-' }}<br>
                    Kode Pos: {{ $order->pos ?? '-' }}
                </td>
                <td width="50%">
                    <strong>Informasi Pengiriman:</strong><br>
                    Kurir: {{ $order->kurir ?? '-' }}<br>
                    Layanan: {{ $order->layanan_ongkir ?? '-' }}<br>
                    Estimasi: {{ $order->estimasi_ongkir ?? '-' }}<br>
                    Berat: {{ number_format($order->total_berat ?? 0, 0, ',', '.') }} Gram<br>
                    No. Resi: {{ $order->noresi ?? '-' }}<br>
                    Status: {{ $order->status }}<br>
                    @if ($order->midtrans_order_id)
                    Metode Bayar: {{ $order->payment_type ?? '-' }}<br>
                    Status Midtrans: {{ $order->transaction_status ?? '-' }}<br>
                    Dibayar: {{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : '-' }}
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @forelse ($order->orderItems as $item)
                @php
                $hargaItem = $item->harga * $item->quantity;
                $subtotal += $hargaItem;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($item->produk)->nama_produk ?? '-' }}</td>
                    <td>Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp. {{ number_format($hargaItem, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada produk pada pesanan ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Subtotal</td>
                <td width="25%">Rp. {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Biaya Ongkir</td>
                <td>Rp. {{ number_format($order->biaya_ongkir ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th class="label">Total Bayar</th>
                <th>Rp. {{ number_format($subtotal + ($order->biaya_ongkir ?? 0), 0, ',', '.') }}</th>
            </tr>
        </table>

        <div class="footer">
            <div class="sign">
                <p>Customer</p>
                <div class="space"></div>
                <p>{{ optional(optional($order->customer)->user)->nama ?? '-' }}</p>
            </div>
            <div class="sign">
                <p>Admin</p>
                <div class="space"></div>
                <p>{{ Auth::user()->nama ?? '-' }}</p>
            </div>
        </div>
    </div>
</body>

</html>
