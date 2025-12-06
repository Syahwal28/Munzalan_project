<!DOCTYPE html>
<html>
<head>
    <title>Data Aset Munzalan</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px 8px; text-align: left; }
        th { background-color: #eee; text-transform: uppercase; }
        h4 { text-align: center; margin-bottom: 20px; }
        .filter { margin-bottom: 10px; font-size: 11px; }
    </style>
</head>
<body>

    <h4>LAPORAN DATA ASET & INVENTARIS MUNZALAN</h4>

    @if($search)
    <div class="filter">
        **Filter Pencarian:** {{ $search }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No Inventaris</th>
                <th>Nama Barang</th>
                <th width="10%">Kategori</th>
                <th width="15%">Penanggung Jawab</th>
                <th width="15%">Lokasi</th>
                <th width="10%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $item)
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_aset }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->kategori }}</td>
                <td>{{ $item->penanggung_jawab }}</td>
                <td>{{ $item->lokasi }}</td>
                <td align="center">{{ $item->jumlah }} {{ $item->satuan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" align="center">Tidak ada data aset ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>