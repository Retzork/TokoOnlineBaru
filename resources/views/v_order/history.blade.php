@extends('v_layouts.app')
@section('content')
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <p>HISTORY</p>
            <h3 class="title">HISTORY PESANAN</h3>
        </div>

        <table class="shopping-cart-table table">
            <thead>
                <tr>
                    <th>ID PESANAN</th>
                    <th>TANGGAL</th>
                    <th>TOTAL BAYAR</th>
                    <th>STATUS</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $row)
                @php
                $statusLabel = match ($row->status) {
                    'pending' => 'Menunggu Pembayaran',
                    'proses' => 'Diproses',
                    'Paid' => 'Dibayar',
                    'Kirim' => 'Dikirim',
                    'Selesai' => 'Selesai',
                    default => ucfirst($row->status),
                };
                $statusClass = match ($row->status) {
                    'pending' => 'warning',
                    'proses' => 'info',
                    'Paid' => 'success',
                    'Kirim' => 'primary',
                    'Selesai' => 'success',
                    default => 'default',
                };
                $tanggal = $row->tanggal ?? $row->paid_at ?? $row->created_at;
                @endphp
                <tr>
                    <td>#{{ $row->id }}</td>
                    <td>{{ $tanggal ? $tanggal->format('d/m/Y H:i') : '-' }}</td>
                    <td>Rp. {{ number_format($row->grand_total, 0, ',', '.') }}</td>
                    <td>
                        <span class="label label-{{ $statusClass }}">{{ $statusLabel }}</span>
                        @if ($row->status === 'pending' && $row->transaction_status)
                        <br><small>{{ ucfirst($row->transaction_status) }}</small>
                        @endif
                    </td>
                    <td>
                        @if ($row->status === 'pending' && $row->midtrans_order_id)
                        <a href="{{ route('order.selectpayment') }}" class="primary-btn btn-sm" style="padding: 5px 10px; background-color: #f8694a;">BAYAR SEKARANG</a>
                        @endif
                        <a href="{{ route('order.invoice', $row->id) }}" class="primary-btn btn-sm" style="padding: 5px 10px;">LIHAT DETAIL</a>
                        <a href="{{ route('order.invoice', $row->id) }}" class="primary-btn btn-sm" style="padding: 5px 10px; background-color: #999;">INVOICE</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada riwayat pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
