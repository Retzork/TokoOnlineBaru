@extends('backend.v_layouts.app')
@section('content')
<!-- contentAwal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-2">{{ $subJudul }}</h5>
                    <div class="mb-2">
                        <a href="{{ route('backend.laporan.formpesananproses') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </a>
                        <a href="{{ route('pesanan.selesai') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-circle-check"></i> Pesanan Selesai
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="zero_config" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Order</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Pelanggan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($index as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>#{{ $row->id }}</td>
                                <td>{{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}</td>
                                <td>Rp. {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $row->status_badge }}">{{ $row->status_label }}</span>
                                    @if ($row->noresi)
                                    <br><small class="text-muted">Resi: {{ $row->noresi }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $row->customer->user->nama ?? '-' }}<br>
                                    <small class="text-muted">{{ $row->customer->user->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('pesanan.detail', $row->id) }}" title="Detail Pesanan">
                                        <button type="button" class="btn btn-primary btn-sm">
                                            <i class="far fa-eye"></i> Detail
                                        </button>
                                    </a>
                                    <a href="{{ route('pesanan.invoice', $row->id) }}" title="Cetak Invoice" target="_blank">
                                        <button type="button" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-print"></i> Invoice
                                        </button>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada pesanan yang sedang diproses.</td>
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
