<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Saldo Barang</title>
<style>
@page { size: A4 portrait; margin: 13mm 12mm; }
* { box-sizing: border-box; }
body { margin: 0; background: #edf1f5; color: #203047; font: 12px/1.45 Arial, sans-serif; }
.sheet { max-width: 900px; margin: 20px auto; padding: 28px; background: #fff; }
header { border-bottom: 3px solid #24465b; padding-bottom: 15px; margin-bottom: 18px; }
.company { font-size: 17px; font-weight: bold; letter-spacing: .3px; }
.address { color: #526477; font-size: 10px; margin-top: 3px; }
.eyebrow { color: #526477; font-size: 10px; text-transform: uppercase; letter-spacing: 1.4px; margin-top: 19px; }
h1 { font-size: 25px; line-height: 1.2; margin: 5px 0 8px; }
.meta { color: #526477; }
.summary { display: table; width: 100%; border-collapse: collapse; margin: 0 0 20px; }
.metric { display: table-cell; width: 50%; padding: 12px 15px; background: #f1f5f7; border: 1px solid #d8e1e7; }
.metric-label { color: #526477; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
.metric-value { font-size: 19px; font-weight: bold; margin-top: 4px; }
table.report { width: 100%; border-collapse: collapse; font-size: 11px; }
th, td { padding: 7px 8px; border-bottom: 1px solid #dce3e9; vertical-align: top; }
thead th { background: #24465b; color: white; text-align: left; font-weight: bold; }
.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
.center { text-align: center; }
.detail:nth-child(even) td { background: #f6f8fa; }
.group th { background: #e8eef2; text-align: left; color: #24465b; font-weight: bold; }
.subtotal td { background: #f1f5f7; font-weight: bold; border-top: 1px solid #aebdc8; }
.grand td { background: #24465b; color: white; font-size: 12px; font-weight: bold; border: 0; padding: 11px 8px; }
.empty { text-align: center; color: #526477; padding: 22px; }
footer { margin-top: 20px; padding-top: 9px; border-top: 1px solid #dce3e9; font-size: 10px; color: #526477; }
.note { margin-top: 12px; font-size: 10px; color: #526477; }
@media print {
 body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
 .sheet { max-width: none; margin: 0; padding: 0; }
 thead { display: table-header-group; }
 tr { break-inside: avoid; page-break-inside: avoid; }
 .group { break-after: avoid; page-break-after: avoid; }
 .summary, header, footer { break-inside: avoid; }
}
</style>
</head>
<body>
@php
    $grouped = (bool) $bygroup;
    $amount = function ($value) {
        return is_object($value) && method_exists($value, 'getAmount') ? $value->getAmount() : ($value ?? 0);
    };
    $money = function ($value) use ($amount) {
        return 'Rp ' . number_format($amount($value), 2, ',', '.');
    };
    $rows = collect($reportData);
    $groups = $rows->groupBy(function ($row) { return $row->groupname ?: 'Tanpa Grup'; });
    $total = $rows->sum(function ($row) use ($amount) { return $amount($row->stock_value); });
    $index = 0;
@endphp
<main class="sheet">
<header>
    <div class="company">CV. Serasi Jaya Sejati</div>
    <div class="address">Jl. Setiabudi No.150, Berohol, Bajenis, Kota Tebing Tinggi, Sumatera Utara 20998</div>
    <div class="eyebrow">Gudang &amp; Persediaan</div>
    <h1>Laporan Saldo Barang</h1>
    <div class="meta">Tanggal pilihan: <strong>{{ date('d/m/Y', strtotime($tanggal)) }}</strong> &nbsp; | &nbsp; {{ $grouped ? 'Ringkasan per grup' : 'Rincian per barang' }}</div>
</header>
<div class="summary">
    <div class="metric"><div class="metric-label">{{ $grouped ? 'Jumlah grup' : 'Jumlah baris barang' }}</div><div class="metric-value">{{ number_format($rows->count(), 0, ',', '.') }}</div></div>
    <div class="metric"><div class="metric-label">Total nilai stok</div><div class="metric-value">{{ $money($total) }}</div></div>
</div>
<table class="report">
<thead><tr>
    <th class="center" style="width: 7%">No.</th>
    <th>{{ $grouped ? 'Grup Barang' : 'Nama Barang' }}</th>
    @unless($grouped)
    <th class="num">Stok</th><th class="center">Satuan</th><th class="num">Harga / Unit</th>
    @endunless
    <th class="num">Nilai Stok</th>
</tr></thead>
<tbody>
@if($grouped)
    @foreach($rows as $row)
    <tr class="detail"><td class="center">{{ ++$index }}</td><td>{{ $row->groupname ?: 'Tanpa Grup' }}</td><td class="num">{{ $money($row->stock_value) }}</td></tr>
    @endforeach
@else
    @foreach($groups as $name => $items)
    <tr class="group"><th colspan="6">{{ $name }}</th></tr>
    @foreach($items as $row)
    <tr class="detail">
        <td class="center">{{ ++$index }}</td><td>{{ $row->inventoryname }}</td>
        <td class="num">{{ $row->in_stock }}</td><td class="center">{{ $row->unit }}</td>
        <td class="num">{{ $money($row->avg_value) }}</td><td class="num">{{ $money($row->stock_value) }}</td>
    </tr>
    @endforeach
    <tr class="subtotal"><td colspan="5" class="num">Subtotal {{ $name }}</td><td class="num">{{ $money($items->sum(function ($row) use ($amount) { return $amount($row->stock_value); })) }}</td></tr>
    @endforeach
@endif
@if($rows->isEmpty())
<tr><td colspan="{{ $grouped ? 3 : 6 }}" class="empty">Tidak ada data saldo barang.</td></tr>
@endif
<tr class="grand"><td colspan="{{ $grouped ? 2 : 5 }}">TOTAL NILAI PERSEDIAAN</td><td class="num">{{ $money($total) }}</td></tr>
</tbody>
</table>
@if(!$grouped)
<div class="note">Catatan: rincian barang menggunakan saldo dan harga rata-rata saat ini dari sistem, bukan rekonstruksi saldo historis pada tanggal pilihan.</div>
@endif
<footer>ERP Gudang SJS &nbsp; | &nbsp; Laporan persediaan &nbsp; | &nbsp; Data diambil dari server.</footer>
</main>
</body>
</html>
