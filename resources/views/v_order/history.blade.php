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
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->tanggal }}</td>
                    <td>Rp. {{ number_format($row->total_harga + $row->biaya_ongkir, 0, ',', '.') }}</td>
                    <td><span class="label label-warning">{{ ucfirst($row->status) }}</span></td>
                    <td>
                        <a href="#" class="primary-btn btn-sm" style="padding: 5px 10px;">LIHAT DETAIL</a>
                        <a href="#" class="primary-btn btn-sm" style="padding: 5px 10px; background-color: #f8694a;">INVOICE</a>
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