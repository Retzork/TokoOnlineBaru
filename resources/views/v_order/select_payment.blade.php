@extends('v_layouts.app')
@section('content')
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <h3 class="title">KERANJANG BELANJA</h3>
        </div>

        <table class="shopping-cart-table table">
            <thead>
                <tr>
                    <th>PRODUK</th>
                    <th class="text-center">HARGA</th>
                    <th class="text-center">QUANTITY</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td class="details">
                        <img src="{{ asset('storage/img-produk/' . $item->produk->foto) }}" width="50" style="float:left; margin-right:10px;">
                        <a href="#">{{ $item->produk->nama_produk }}</a><br>
                        <small>Berat: {{ $item->produk->berat }} Gram</small><br>
                        <small>Stok: {{ $item->produk->stok }} Gram</small>
                    </td>
                    <td class="price text-center"><strong>Rp. {{ number_format($item->harga, 0, ',', '.') }}</strong></td>
                    <td class="qty text-center"><strong>{{ $item->quantity }}</strong></td>
                    <td class="total text-right"><strong class="primary-color">Rp. {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="empty" colspan="2"></th>
                    <th>SUBTOTAL</th>
                    <th class="text-right">Rp. {{ number_format($order->total_harga, 0, ',', '.') }}</th>
                </tr>
                <tr>
                    <th class="empty" colspan="2"></th>
                    <th>Ongkos Kirim</th>
                    <td class="text-right">
                        <small>Rp. {{ number_format($order->biaya_ongkir, 0, ',', '.') }}</small><br>
                        <small>{{ $order->kurir }} {{ $order->layanan_ongkir }} ({{ $order->estimasi_ongkir }})</small>
                    </td>
                </tr>
                <tr>
                    <th class="empty" colspan="2"></th>
                    <th>TOTAL BAYAR</th>
                    <th class="text-right"><strong class="primary-color" style="font-size: 24px;">Rp. {{ number_format($order->total_harga + $order->biaya_ongkir, 0, ',', '.') }}</strong></th>
                </tr>
            </tfoot>
        </table>

        <div class="pull-right">
            <form action="{{ route('order.checkoutProses') }}" method="post">
                @csrf
                <button type="submit" class="primary-btn">BAYAR SEKARANG</button>
            </form>
        </div>
    </div>
</div>
@endsection