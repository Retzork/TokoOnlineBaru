@extends('backend.v_layouts.app')
@section('content')
<!-- contentAwal -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $judul }}<br><br></h5>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="35%">ID Order</th>
                                <td>#{{ $order->id }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ ($order->tanggal ?? $order->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Pelanggan</th>
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
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="35%">Kurir</th>
                                <td>{{ $order->kurir ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Layanan</th>
                                <td>{{ $order->layanan_ongkir ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Estimasi</th>
                                <td>{{ $order->estimasi_ongkir ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Berat</th>
                                <td>{{ number_format($order->total_berat ?? 0, 0, ',', '.') }} Gram</td>
                            </tr>
                            <tr>
                                <th>No Resi</th>
                                <td>{{ $order->noresi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge badge-info">{{ $order->status }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h4 class="card-title mt-4">Daftar Produk</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
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
                                <td colspan="5" class="text-center">Tidak ada produk pada pesanan ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Subtotal</th>
                                <th>Rp. {{ number_format($subtotal, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Biaya Ongkir</th>
                                <th>Rp. {{ number_format($order->biaya_ongkir ?? 0, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Total Bayar</th>
                                <th>Rp. {{ number_format($subtotal + ($order->biaya_ongkir ?? 0), 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h4 class="card-title mt-4">Ubah Data Pesanan</h4>
                <form class="form-horizontal" action="{{ route('pesanan.update', $order->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Resi</label>
                                <input type="text" name="noresi" value="{{ old('noresi', $order->noresi) }}" class="form-control @error('noresi') is-invalid @enderror" placeholder="Masukkan Nomor Resi">
                                @error('noresi')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Status Pesanan</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="proses" {{ old('status', $order->status) == 'proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="Paid" {{ old('status', $order->status) == 'Paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="Kirim" {{ old('status', $order->status) == 'Kirim' ? 'selected' : '' }}>Kirim</option>
                                    <option value="Selesai" {{ old('status', $order->status) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('status')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" name="pos" value="{{ old('pos', $order->pos) }}" class="form-control @error('pos') is-invalid @enderror" placeholder="Masukkan Kode Pos">
                                @error('pos')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Alamat Pengiriman</label>
                                <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror" placeholder="Masukkan Alamat Pengiriman">{{ old('alamat', $order->alamat) }}</textarea>
                                @error('alamat')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('pesanan.invoice', $order->id) }}" class="btn btn-warning" target="_blank">Cetak Invoice</a>
                    <a href="{{ route('pesanan.proses') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- contentAkhir -->
@endsection
