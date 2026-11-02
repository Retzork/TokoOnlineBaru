@extends('backend.v_layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body border-top">
                <!-- Judul Halaman -->
                <h5 class="card-title">{{ $judul }}</h5>

                <!-- Pesan Selamat Datang -->
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Selamat Datang, {{ Auth::user()->nama ?? 'Pengguna' }}</h4>
                    Aplikasi Toko Online dengan hak akses yang Anda miliki sebagai
                    <b>
                        @if ((int) Auth::user()->role === 1)
                            Super Admin
                        @elseif((int) Auth::user()->role === 0)
                            Admin
                        @else
                            Pengguna
                        @endif
                    </b>.
                    Ini adalah halaman utama dari aplikasi Web Programming: Studi Kasus Toko Online.
                    <hr>
                    <p class="mb-0">Kuliah..? BSI Aja !!!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu statistik -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="mr-3 text-center">
                        <i class="fa-solid fa-tag text-info" style="font-size: 32px;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($totalProduk, 0, ',', '.') }}</h4>
                        <small class="text-muted">Total Produk</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="mr-3 text-center">
                        <i class="fa-solid fa-users text-success" style="font-size: 32px;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($totalCustomer, 0, ',', '.') }}</h4>
                        <small class="text-muted">Total Customer</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="mr-3 text-center">
                        <i class="fa-solid fa-truck-fast text-warning" style="font-size: 32px;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($pesananProses, 0, ',', '.') }}</h4>
                        <small class="text-muted">Pesanan Proses</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="mr-3 text-center">
                        <i class="fa-solid fa-circle-check text-primary" style="font-size: 32px;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($pesananSelesai, 0, ',', '.') }}</h4>
                        <small class="text-muted">Pesanan Selesai</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik pesanan (Minggu 11) -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <h4 class="card-title">Grafik Pesanan Tahun {{ $tahun }}</h4>
                    <div class="ml-auto">
                        <small class="text-muted">
                            Total Pendapatan (Selesai):
                            <b>Rp. {{ number_format($totalPendapatan, 0, ',', '.') }}</b>
                        </small>
                    </div>
                </div>

                <div id="grafik-pesanan" style="width: 100%; height: 300px;"></div>
                <p class="text-muted mt-2 mb-0">
                    <small>Grafik jumlah pesanan yang masuk setiap bulan (status selain keranjang/pending).</small>
                </p>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Konten -->
@endsection

@push('scripts')
<script src="{{ asset('backend/libs/chart/jquery.flot.min.js') }}"></script>
<script src="{{ asset('backend/libs/chart/jquery.flot.resize.min.js') }}"></script>
<script src="{{ asset('backend/libs/flot/jquery.flot.categories.js') }}"></script>
<script>
    $(function() {
        var dataPesanan = @json($grafikPesanan);
        var labelBulan = @json(array_column($grafikPesanan, 0));

        var plot = $.plot($('#grafik-pesanan'), [{
            data: dataPesanan,
            label: 'Jumlah Pesanan',
            color: '#2962ff',
            lines: {
                show: true,
                fill: true,
                fillColor: 'rgba(41, 98, 255, 0.12)'
            },
            points: {
                show: true,
                radius: 4,
                fill: true,
                fillColor: '#ffffff'
            }
        }], {
            series: {
                shadowSize: 0
            },
            grid: {
                borderWidth: 1,
                borderColor: '#e5e5e5',
                hoverable: true,
                backgroundColor: '#ffffff'
            },
            xaxis: {
                mode: 'categories',
                tickLength: 0
            },
            yaxis: {
                min: 0,
                tickDecimals: 0
            }
        });

        var previousPoint = null;
        $('#grafik-pesanan').bind('plothover', function(event, pos, item) {
            if (item) {
                if (previousPoint !== item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $('#grafik-tooltip').remove();
                    var x = labelBulan[item.dataIndex] !== undefined ? labelBulan[item.dataIndex] : item.datapoint[0],
                        y = item.datapoint[1];
                    $('<div id="grafik-tooltip">' + item.series.label + ' ' + x + ': <b>' + y + '</b></div>')
                        .css({
                            top: item.pageY + 5,
                            left: item.pageX + 5,
                            position: 'absolute',
                            display: 'block',
                            border: '1px solid #ccc',
                            padding: '4px 8px',
                            background: '#fff',
                            opacity: 0.9,
                            'font-size': '12px',
                            'z-index': 9999
                        })
                        .appendTo('body');
                }
            } else {
                $('#grafik-tooltip').remove();
                previousPoint = null;
            }
        });
    });
</script>
@endpush
@extends('backend.v_layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body border-top">
                <!-- Judul Halaman -->
                <h5 class="card-title">{{ $judul }}</h5>

                <!-- Pesan Selamat Datang -->
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Selamat Datang, {{ Auth::user()->nama ?? 'Pengguna' }}</h4>
                    Aplikasi Toko Online dengan hak akses yang Anda miliki sebagai 
                    <b>
                        @if (Auth::user()->role === 1)
                            Super Admin
                        @elseif(Auth::user()->role === 0)
                            Admin
                        @else
                            Pengguna
                        @endif
                    </b>.
                    Ini adalah halaman utama dari aplikasi Web Programming: Studi Kasus Toko Online.
                    <hr>
                    <p class="mb-0">Kuliah..? BSI Aja !!!</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Konten -->
@endsection
