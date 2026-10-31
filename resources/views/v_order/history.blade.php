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
                <tr>
                    <td>#{{ $row->id }}</td>
                    <td>
                        @if ($row->tanggal)
                            {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y H:i') }}
                        @else
                            {{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}
                        @endif
                    </td>
                    <td>Rp. {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                    <td><span class="label {{ $row->status_badge }}">{{ $row->status_label }}</span></td>
                    <td>
                        <a href="{{ route('order.invoice', $row->id) }}" class="primary-btn btn-sm" style="padding: 5px 10px;">
                            LIHAT DETAIL
                        </a>
                        <a href="{{ route('order.invoice', $row->id) }}" class="primary-btn btn-sm" style="padding: 5px 10px; background-color: #f8694a;">
                            INVOICE
                        </a>
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
