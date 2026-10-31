@extends('backend.v_layouts.app')
@section('content')
<!-- contentAwal -->
@php
    $pelanggan = optional($order->customer)->user;
    $alamatValue = old('alamat', str_replace(['<br>', '<br/>', '<br />'], "\n", strip_tags($order->alamat ?? '')));
    $totalHarga = 0;
    $totalBerat = 0;
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $subJudul }} #{{ $order->id }}</h4>
                    <small class="text-muted">
                        Tanggal: {{ $order->created_at ? $order->created_at->format('d M Y H:i') : '-' }}
                    </small>
                </div>
                <div>
                    <span class="badge {{ $order->status_badge }}">{{ $order->status_label }}</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('pesanan.update', $order->id) }}" method="post">
                    @method('put')
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Pelanggan</h5>
                            <address>
                                Nama: {{ $pelanggan->nama ?? '-' }}<br>
                                Email: {{ $pelanggan->email ?? '-' }}<br>
                                HP: {{ $pelanggan->hp ?? '-' }}<br>
                                Kode Pos: {{ $order->pos ?: '-' }}
                            </address>
                        </div>
                        <div class="col-md-6 text-right">
                            <h5>Ongkos Kirim</h5>
                            <address>
                                Kurir: {{ $order->kurir ?: '-' }}<br>
                                Layanan: {{ $order->layanan_ongkir ?: '-' }}<br>
                                Estimasi: {{ $order->estimasi_ongkir ?: '-' }} Hari<br>
                                Berat: {{ $order->total_berat ?: $order->orderItems->sum(fn ($item) => $item->produk->berat * $item->quantity) }} Gram<br>
                                No. Resi: {{ $order->noresi ?: '-' }}<br>
                                Status: {{ $order->status_label }}
                            </address>
                        </div>
                    </div>

                    <h5>Produk</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th colspan="2">Produk</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->orderItems as $item)
                                @php
                                    $totalHarga += $item->harga * $item->quantity;
                                    $totalBerat += $item->produk->berat * $item->quantity;
                                    $foto = $item->produk->foto;
                                @endphp
                                <tr>
                                    <td class="text-center" width="90">
                                        <img src="{{ asset('storage/img-produk/thumb_sm_' . $foto) }}"
                                             alt="{{ $item->produk->nama_produk }}"
                                             width="60"
                                             onerror="this.src='{{ asset('image/img-default.jpg') }}'">
                                    </td>
                                    <td class="details">
                                        <a>{{ $item->produk->nama_produk }} #{{ $item->produk->kategori->nama_kategori ?? '-' }}</a>
                                        <ul class="mb-0">
                                            <li><span>Berat: {{ $item->produk->berat }} Gram</span></li>
                                            <li><span>Stok: {{ $item->produk->stok }}</span></li>
                                        </ul>
                                    </td>
                                    <td class="price text-center">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="qty text-center">{{ $item->quantity }}</td>
                                    <td class="total text-center">Rp. {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Pesanan ini belum memiliki item produk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="empty" colspan="3"></th>
                                    <td>Subtotal</td>
                                    <td class="text-center">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="empty" colspan="3"></th>
                                    <td>Ongkos Kirim</td>
                                    <td class="text-center">Rp. {{ number_format($order->biaya_ongkir, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="empty" colspan="3"></th>
                                    <th>TOTAL BAYAR</th>
                                    <th class="text-center">Rp. {{ number_format($order->total_bayar, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. Resi</label>
                                <input type="text" name="noresi" value="{{ old('noresi', $order->noresi) }}"
                                       class="form-control @error('noresi') is-invalid @enderror"
                                       placeholder="Masukkan Nomor Resi">
                                @error('noresi')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    @if (!in_array($order->status, ['Paid', 'Kirim', 'Selesai']))
                                    <option value="{{ $order->status }}" selected disabled>
                                        {{ $order->status_label }} (data lama)
                                    </option>
                                    @endif
                                    <option value="Paid" {{ old('status', $order->status) == 'Paid' ? 'selected' : '' }}>Proses</option>
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
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                                          id="ckeditor" rows="4">{{ $alamatValue }}</textarea>
                                @error('alamat')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" name="pos" value="{{ old('pos', $order->pos) }}"
                                       class="form-control @error('pos') is-invalid @enderror"
                                       placeholder="Masukkan Kode Pos">
                                @error('pos')
                                <span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('pesanan.invoice', $order->id) }}" target="_blank" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Cetak Invoice
                    </a>
                    <a href="{{ route('pesanan.proses') }}" class="btn btn-light">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- contentAkhir -->
@endsection
