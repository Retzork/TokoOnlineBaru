@extends('v_layouts.app')
@section('content')
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <p>PEMBAYARAN</p>
            <h3 class="title">Pembayaran Pesanan #{{ $order->id }}</h3>
        </div>

        @if (!empty($paymentError))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>{{ $paymentError }}</strong>
        </div>
        @endif

        @if ($order->orderItems->count() > 0)
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
                        <img src="{{ asset('storage/img-produk/thumb_sm_' . $item->produk->foto) }}" alt="" style="float:left; margin-right:10px;">
                        <a>{{ $item->produk->nama_produk }}</a><br>
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
                    <th>ONGKOS KIRIM</th>
                    <td class="text-right">
                        <small>Rp. {{ number_format($order->biaya_ongkir ?? 0, 0, ',', '.') }}</small><br>
                        <small>{{ $order->kurir }} {{ $order->layanan_ongkir }} @if($order->estimasi_ongkir) (estimasi {{ $order->estimasi_ongkir }} hari) @endif</small>
                    </td>
                </tr>
                <tr>
                    <th class="empty" colspan="2"></th>
                    <th>TOTAL BAYAR</th>
                    <th class="text-right"><strong class="primary-color" style="font-size: 24px;">Rp. {{ number_format($order->grand_total, 0, ',', '.') }}</strong></th>
                </tr>
            </tfoot>
        </table>

        <div class="row" style="margin-top: 20px;">
            <div class="col-md-6">
                <h4>Alamat Pengiriman</h4>
                <p>{!! $order->alamat ?? '-' !!}<br>Kode Pos: {{ $order->pos ?? '-' }}</p>
            </div>
            <div class="col-md-6 text-right">
                @if (!empty($snapToken))
                <p style="margin-bottom: 10px;">Pilih metode pembayaran (transfer bank, kartu kredit, e-wallet, dll.) pada jendela Midtrans.</p>
                <button type="button" class="primary-btn" id="pay-button">BAYAR SEKARANG</button>
                @else
                <a href="{{ route('order.cart') }}" class="primary-btn" style="background-color: #999;">KEMBALI KE KERANJANG</a>
                @endif
            </div>
        </div>
        @else
        <p>Keranjang belanja kosong.</p>
        @endif
    </div>
</div>

@if (!empty($snapToken))
<script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
(function () {
    var payButton = document.getElementById('pay-button');
    if (!payButton || typeof window.snap === 'undefined') {
        return;
    }

    var snapToken = @json($snapToken);
    var historyUrl = @json(route('order.history'));
    var completeUrl = @json(route('order.complete'));

    payButton.addEventListener('click', function () {
        payButton.disabled = true;
        payButton.textContent = 'MEMPROSES...';

        window.snap.pay(snapToken, {
            onSuccess: function (result) {
                window.location.href = completeUrl;
            },
            onPending: function (result) {
                alert('Pembayaran Anda sedang menunggu penyelesaian. Silakan selesaikan pembayaran.');
                window.location.href = historyUrl;
            },
            onError: function (result) {
                alert('Pembayaran gagal. Silakan coba lagi.');
                payButton.disabled = false;
                payButton.textContent = 'BAYAR SEKARANG';
            },
            onClose: function () {
                payButton.disabled = false;
                payButton.textContent = 'BAYAR SEKARANG';
            }
        });
    });
})();
</script>
@endif
@endsection
