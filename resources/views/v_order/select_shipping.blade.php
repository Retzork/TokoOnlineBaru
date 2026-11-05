@extends('v_layouts.app')
@section('content')
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <h3 class="title">PILIH PENGIRIMAN</h3>
        </div>

        <form id="form-ongkir">
            <div class="form-group">
                <label>Provinsi Tujuan:</label>
                <select class="form-control" name="province_id" id="province_id">
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kota Tujuan:</label>
                <select class="form-control" name="city_id" id="city_id">
                    <option value="">-- Pilih Kota --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kurir:</label>
                <select class="form-control" name="courier" id="courier">
                    <option value="">-- Pilih Kurir --</option>
                    <option value="jne">JNE</option>
                    <option value="tiki">TIKI</option>
                    <option value="pos">POS Indonesia</option>
                </select>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea class="form-control" name="alamat" id="alamat" required placeholder="Jl. Sawo No. 4 RT/RW 09/10"></textarea>
            </div>
            <div class="form-group">
                <label>Kode Pos</label>
                <input type="text" class="form-control" name="pos" id="pos" required placeholder="52114">
            </div>

            <input type="hidden" id="weight" value="{{ $order->orderItems->sum(fn($item) => $item->produk->berat * $item->quantity) }}">
            <button type="button" class="primary-btn" id="btn-cek-ongkir">CEK ONGKIR</button>
        </form>

        <div id="hasil-ongkir" style="margin-top: 30px; display:none;">
            <table class="shopping-cart-table table">
                <thead>
                    <tr>
                        <th>LAYANAN</th>
                        <th>BIAYA</th>
                        <th>ESTIMASI PENGIRIMAN</th>
                        <th>TOTAL BERAT</th>
                        <th>TOTAL HARGA</th>
                        <th>BAYAR</th>
                    </tr>
                </thead>
                <tbody id="tabel-layanan">
                    <!-- Data dari AJAX akan masuk di sini -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Load Provinsi
    $.get("{{ route('order.provinces') }}", function(data) {
        let results = data.rajaongkir.results;
        results.forEach(function(item) {
            $('#province_id').append(`<option value="${item.province_id}">${item.province}</option>`);
        });
    });

    // Load Kota
    $('#province_id').change(function() {
        let provinceId = $(this).val();
        $('#city_id').html('<option value="">-- Pilih Kota --</option>');
        if(provinceId) {
            $.get("{{ route('order.cities') }}?province_id=" + provinceId, function(data) {
                let results = data.rajaongkir.results;
                results.forEach(function(item) {
                    $('#city_id').append(`<option value="${item.city_id}">${item.city_name}</option>`);
                });
            });
        }
    });

    // Cek Ongkir & Tampilkan Tabel
    $('#btn-cek-ongkir').click(function() {
        let origin = 153; // Sesuaikan ID Kota asal toko Anda
        let destination = $('#city_id').val();
        let weight = $('#weight').val();
        let courier = $('#courier').val();
        let alamat = $('#alamat').val();
        let pos = $('#pos').val();

        if(!destination || !courier || !alamat || !pos) {
            alert('Lengkapi semua data tujuan, alamat, dan kurir!');
            return;
        }

        $.post("{{ route('order.cost') }}", {
            _token: "{{ csrf_token() }}",
            origin: origin,
            destination: destination,
            weight: weight,
            courier: courier
        }, function(data) {
            let services = data.rajaongkir.results;
            let courierName = $('#courier option:selected').text();
            let totalHarga = parseInt("{{ $order->total_harga }}") || 0;
            
            $('#tabel-layanan').empty();
            
            if (services && services.length > 0) {
                services.forEach(function(s) {
                    let biaya = 0;
                    let estimasi = '';
                    
                    if (s.cost && Array.isArray(s.cost) && s.cost.length > 0) {
                        biaya = s.cost[0].value;
                        estimasi = s.cost[0].etd;
                    }

                    let totalBayar = totalHarga + biaya;
                    
                    let row = `
                        <tr>
                            <td>${s.service}</td>
                            <td>${biaya} Rupiah</td>
                            <td>${estimasi} hari</td>
                            <td>${weight} Gram</td>
                            <td>Rp. ${totalBayar.toLocaleString('id-ID')}</td>
                            <td>
                                <form action="{{ route('order.updateongkir') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="kurir" value="${courierName}">
                                    <input type="hidden" name="layanan_ongkir" value="${s.service}">
                                    <input type="hidden" name="biaya_ongkir" value="${biaya}">
                                    <input type="hidden" name="estimasi_ongkir" value="${estimasi}">
                                    <input type="hidden" name="total_berat" value="${weight}">
                                    <input type="hidden" name="alamat" value="${alamat}">
                                    <input type="hidden" name="pos" value="${pos}">
                                    <input type="hidden" name="province_name" value="${$("#province_id option:selected").text()}">
                                    <input type="hidden" name="city_name" value="${$("#city_id option:selected").text()}">
                                    <button type="submit" class="primary-btn">PILIH PENGIRIMAN</button>
                                </form>
                            </td>
                        </tr>
                    `;
                    $('#tabel-layanan').append(row);
                });
                $('#hasil-ongkir').show();
            } else {
                alert('Tidak ada layanan pengiriman yang tersedia untuk rute/kurir ini.');
            }
        }).fail(function() {
            alert('Gagal mengambil data ongkir.');
        });
    });
});
</script>
@endsection