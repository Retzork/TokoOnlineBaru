@extends('v_layouts.app')
@section('content')
<!-- template -->

<style>
    .invoice-table th,
    .invoice-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .invoice-table th {
        background: #f5f5f5;
    }

    @media print {
        #top-header,
        #header,
        #navigation,
        #aside,
        #footer,
        .no-print {
            display: none !important;
        }

        #main {
            width: 100% !important;
            float: none !important;
        }
    }
</style>

<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <p>INVOICE</p>
            <h3 class="title">Invoice Pesanan #{{ $order->id }}</h3>
        </div>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="35%">Ditagihkan Kepada</th>
                        <td>{{ optional(optional($order->customer)->user)->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ optional(optional($order->customer)->user)->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td>{{ optional(optional($order->customer)->user)->hp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $order->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kode Pos</th>
                        <td>{{ $order->pos ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="35%">Tanggal</th>
                        <td>{{ ($order->tanggal ?? $order->created_at)->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Kurir</th>
                        <td>{{ $order->kurir ?? '-' }} {{ $order->layanan_ongkir ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Estimasi</th>
                        <td>{{ $order->estimasi_ongkir ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. Resi</th>
                        <td>{{ $order->noresi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><span class="label label-warning">{{ ucfirst($order->status) }}</span></td>
                    </tr>
                    @if ($order->midtrans_order_id)
                    <tr>
                        <th>Metode Bayar</th>
                        <td>{{ $order->payment_type ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td>{{ $order->transaction_status ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dibayar Pada</th>
                        <td>{{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <table class="shopping-cart-table table invoice-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Subtotal</th>
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
                    <td class="text-center">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">Rp. {{ number_format($hargaItem, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada produk pada pesanan ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Subtotal</th>
                    <th class="text-right">Rp. {{ number_format($subtotal, 0, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Biaya Ongkir</th>
                    <th class="text-right">Rp. {{ number_format($order->biaya_ongkir ?? 0, 0, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Total Bayar</th>
                    <th class="text-right">Rp. {{ number_format($subtotal + ($order->biaya_ongkir ?? 0), 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="pull-right no-print">
            <button type="button" class="primary-btn" onclick="window.print()">Cetak Invoice</button>
            <a href="{{ route('order.history') }}" class="primary-btn" style="background-color: #999;">Kembali</a>
        </div>
    </div>
</div>

<!-- end template-->
@endsection
