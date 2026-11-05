@extends('backend.v_layouts.app')
@section('content')
<!-- contentAwal -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $judul }}<br><br></h5>

                <div class="table-responsive">
                    <table id="zero_config" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Order</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>No Resi</th>
                                <th>Pelanggan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($index as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>#{{ $row->id }}</td>
                                <td>{{ ($row->tanggal ?? $row->created_at)->format('d M Y H:i') }}</td>
                                <td>Rp. {{ number_format($row->total_harga + $row->biaya_ongkir, 0, ',', '.') }}</td>
                                <td>{{ $row->noresi ?? '-' }}</td>
                                <td>{{ optional(optional($row->customer)->user)->email ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('pesanan.detail', $row->id) }}" title="Detail Order">
                                        <button type="button" class="btn btn-cyan btn-sm"><i class="fas fa-eye"></i> Detail</button>
                                    </a>
                                    <a href="{{ route('pesanan.invoice', $row->id) }}" title="Cetak Invoice" target="_blank">
                                        <button type="button" class="btn btn-warning btn-sm"><i class="fas fa-print"></i> Cetak</button>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada pesanan yang selesai.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- contentAkhir -->
@endsection
