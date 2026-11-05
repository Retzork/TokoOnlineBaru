<style>
    table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #ccc;
    }
    table tr td {
        padding: 6px;
        font-weight: normal;
        border: 1px solid #ccc;
    }
    table th {
        border: 1px solid #ccc;
    }
</style>
<table>
    <tr>
        <td align="left">
            Perihal : {{ $judul }} <br>
            Tanggal Awal: {{ $tanggalAwal }} s/d Tanggal Akhir: {{ $tanggalAkhir }}
        </td>
    </tr>
</table>
<p></p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>ID Order</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Alamat</th>
            <th>Kurir</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cetak as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>#{{ $row->id }}</td>
            <td>{{ ($row->tanggal ?? $row->created_at)->format('d/m/Y H:i') }}</td>
            <td>{{ optional(optional($row->customer)->user)->nama ?? '-' }}</td>
            <td>{{ $row->alamat ?? '-' }}</td>
            <td>{{ $row->kurir ?? '-' }} {{ $row->layanan_ongkir ?? '' }}</td>
            <td>Rp. {{ number_format($row->total_harga + $row->biaya_ongkir, 0, ',', '.') }}</td>
            <td>{{ $row->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<script>
    window.onload = function() {
        printStruk();
    }
    function printStruk() {
        window.print();
    }
</script>
