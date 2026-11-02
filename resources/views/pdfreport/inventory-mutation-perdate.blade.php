<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Mutasi Barang</title>
<style>
  @page { size: A4 portrait; margin: 12mm; }
  * { box-sizing: border-box; }
  body { margin: 0 auto; padding: 24px; max-width: 1100px; color: #1f2937; font: 12px/1.5 Arial, sans-serif; background: #fff; }
  h1 { margin: 0 0 6px; font-size: 22px; }
  .period { color: #596477; margin-bottom: 24px; }
  .report-section { margin: 0 0 28px; break-inside: avoid-page; }
  .section-title { margin: 0 0 8px; padding: 9px 11px; background: #eef2f7; border-left: 4px solid #5b6fc7; font-size: 14px; }
  .table-wrap { width: 100%; overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 7px 8px; border-bottom: 1px solid #e1e6ed; vertical-align: top; }
  th { color: #44516a; text-align: left; background: #f8fafc; }
  .num { text-align: right; white-space: nowrap; }
  .date-row td { background: #f4f6f9; color: #344054; font-weight: bold; }
  .total td { background: #eef2f7; font-weight: bold; border-top: 2px solid #a8b2c3; }
  .empty { padding: 18px; color: #64748b; text-align: center; }
  @media print { body { padding: 0; max-width: none; } .report-section { break-inside: auto; } thead { display: table-header-group; } tr { break-inside: avoid; } }
</style>
</head>
<body>
<h1>Laporan Mutasi Barang</h1>
<div class="period">Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d-m-Y') }}</div>
@php
  $definitions = [
    'receive' => ['Penerimaan Stok — Detail', false, false],
    'receiveSum' => ['Penerimaan Stok — Rangkuman', true, false],
    'nsreceive' => ['Penerimaan Non-Stok — Detail', false, false],
    'nsreceiveSum' => ['Penerimaan Non-Stok — Rangkuman', true, false],
    'consume' => ['Pemakaian Stok — Detail', false, true],
    'consumeSum' => ['Pemakaian Stok — Rangkuman', true, true],
    'nsconsume' => ['Pemakaian Non-Stok — Detail', false, false],
    'nsconsumeSum' => ['Pemakaian Non-Stok — Rangkuman', true, false],
  ];
@endphp
@foreach($definitions as $key => $definition)
  @if(in_array($key, $sections))
    @php
      $rows = $reportData[$key] ?? collect();
      $isSummary = $definition[1];
      $isStockConsume = $definition[2];
      $sectionTotal = 0;
      $previousDate = null;
    @endphp
    <section class="report-section" data-section="{{ $key }}">
      <h2 class="section-title">{{ $definition[0] }}</h2>
      <div class="table-wrap"><table>
        <thead><tr>
          <th>Nama Barang</th><th class="num">Jumlah</th><th>Satuan</th><th class="num">Harga per Unit</th><th class="num">Total Nilai</th>
        </tr></thead>
        <tbody>
        @forelse($rows as $row)
          @php
            $date = $row->post_date ?? null;
            $qty = (float) $row->amount * ($isStockConsume ? -1 : 1);
            $value = (float) $row->total * ($isStockConsume ? -1 : 1);
            $sectionTotal += $value;
          @endphp
          @if(!$isSummary && $date && $date !== $previousDate)
            <tr class="date-row"><td colspan="5">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</td></tr>
            @php($previousDate = $date)
          @endif
          <tr>
            <td>{{ $row->name }}</td>
            <td class="num">{{ number_format($qty, 2, ',', '.') }}</td>
            <td>{{ $row->unit }}</td>
            <td class="num">Rp {{ number_format(abs((float) $row->price), 2, ',', '.') }}</td>
            <td class="num">Rp {{ number_format($value, 2, ',', '.') }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Tidak ada data pada periode ini.</td></tr>
        @endforelse
        <tr class="total"><td colspan="4">TOTAL {{ $definition[0] }}</td><td class="num">Rp {{ number_format($sectionTotal, 2, ',', '.') }}</td></tr>
        </tbody>
      </table></div>
    </section>
  @endif
@endforeach
</body>
</html>
