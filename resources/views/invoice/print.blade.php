@php
    $pelanggan = optional($order->customer)->user;
    $alamat = nl2br(e(str_replace(['<br>', '<br/>', '<br />'], "\n", $order->alamat ?? '')));
    $totalHarga = $order->orderItems->sum(fn ($item) => $item->harga * $item->quantity);
    $totalBerat = $order->orderItems->sum(fn ($item) => $item->produk->berat * $item->quantity);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->id }} — Jajanan Tradisional</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            background: #f4f4f4;
            color: #30323a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-card {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px;
            background: #ffffff;
            border: 1px solid #e3e3e3;
            border-radius: 6px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8694a;
        }
        .invoice-header h1 {
            margin: 0 0 4px;
            color: #f8694a;
            font-size: 30px;
            letter-spacing: 2px;
        }
        .invoice-header p { margin: 2px 0; }
        .logo { width: 170px; max-width: 100%; margin-bottom: 8px; }
        .text-right { text-align: right; }
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin: 24px 0;
        }
        .invoice-meta h3 {
            margin: 0 0 6px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8a8a8a;
        }
        .invoice-meta p { margin: 2px 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            padding: 9px 10px;
            border: 1px solid #e3e3e3;
            vertical-align: middle;
        }
        thead th {
            background: #30323a;
            color: #ffffff;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        tbody tr:nth-child(even) { background: #fafafa; }
        .product { display: flex; align-items: center; gap: 10px; }
        .product img {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e3e3e3;
        }
        .product small { color: #7a7a7a; display: block; }
        .qty, .price, .subtotal { text-align: center; white-space: nowrap; }
        tfoot th, tfoot td { border: 1px solid #e3e3e3; }
        tfoot .label { text-align: right; }
        tfoot .grand-total {
            background: #fff3ef;
            color: #f8694a;
            font-size: 16px;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-primary { background: #007bff; }
        .badge-info { background: #17a2b8; }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #30323a; }
        .badge-secondary { background: #6c757d; }
        .invoice-footer {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-top: 26px;
            padding-top: 16px;
            border-top: 1px solid #e3e3e3;
        }
        .invoice-footer .thanks { max-width: 520px; color: #6f6f6f; }
        .actions { display: flex; gap: 10px; align-items: flex-start; }
        .btn {
            display: inline-block;
            padding: 9px 16px;
            border: 0;
            border-radius: 4px;
            background: #f8694a;
            color: #ffffff;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-secondary { background: #30323a; }
        @media print {
            body { padding: 0; background: #ffffff; }
            .invoice-card { max-width: none; border: 0; border-radius: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="invoice-header">
            <div>
                <img src="{{ asset('image/logo.png') }}" alt="Jajanan Tradisional" class="logo">
                <p><strong>Jajanan Tradisional</strong></p>
                <p>Toko Jajanan Pasar &amp; Makanan Khas Indonesia</p>
            </div>
            <div class="text-right">
                <h1>INVOICE</h1>
                <p><strong>#INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                <p>{{ $order->created_at ? $order->created_at->format('d M Y H:i') : '-' }}</p>
                <p><span class="badge {{ $order->status_badge }}">{{ $order->status_label }}</span></p>
            </div>
        </div>

        <div class="invoice-meta">
            <div>
                <h3>Ditagihkan Kepada</h3>
                <p><strong>{{ $pelanggan->nama ?? '-' }}</strong></p>
                <p>{{ $pelanggan->email ?? '-' }}</p>
                <p>{{ $pelanggan->hp ?? '-' }}</p>
                <p>{!! $alamat !!}</p>
            </div>
            <div class="text-right">
                <h3>Informasi Pengiriman</h3>
                <p>Kurir: {{ $order->kurir ?: '-' }}</p>
                <p>Layanan: {{ $order->layanan_ongkir ?: '-' }}</p>
                <p>Estimasi: {{ $order->estimasi_ongkir ?: '-' }} Hari</p>
                <p>Berat: {{ $order->total_berat ?: $totalBerat }} Gram</p>
                <p>No. Resi: {{ $order->noresi ?: '-' }}</p>
                <p>Kode Pos: {{ $order->pos ?: '-' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="price">Harga</th>
                    <th class="qty">Qty</th>
                    <th class="subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->orderItems as $item)
                <tr>
                    <td>
                        <div class="product">
                            <img src="{{ asset('storage/img-produk/thumb_sm_' . $item->produk->foto) }}"
                                 alt="{{ $item->produk->nama_produk }}"
                                 onerror="this.src='{{ asset('image/img-default.jpg') }}'">
                            <div>
                                {{ $item->produk->nama_produk }}
                                <small>Berat: {{ $item->produk->berat }} Gram</small>
                            </div>
                        </div>
                    </td>
                    <td class="price">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="subtotal">Rp. {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada item pada pesanan ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="label">Subtotal</td>
                    <td class="subtotal">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="label">Ongkos Kirim</td>
                    <td class="subtotal">Rp. {{ number_format($order->biaya_ongkir, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th colspan="3" class="label">TOTAL BAYAR</th>
                    <th class="subtotal grand-total">Rp. {{ number_format($order->total_bayar, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="invoice-footer">
            <div class="thanks">
                <p><strong>Terima kasih atas pesanan Anda.</strong></p>
                <p>Invoice ini dibuat otomatis oleh sistem Toko Online Jajanan Tradisional.</p>
            </div>
            <div class="actions no-print">
                <button type="button" class="btn" onclick="window.print()">
                    Cetak Invoice
                </button>
                @if (!empty($backUrl))
                <a href="{{ $backUrl }}" class="btn btn-secondary">{{ $backLabel ?? 'Kembali' }}</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
