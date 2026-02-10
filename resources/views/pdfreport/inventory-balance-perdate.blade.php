<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terima Barang</title>
    <style>
        @page {
          margin: 0;
          margin-top: 30;
        }
        .clearfix:after {
        content: "";
        display: table;
        clear: both;
        }

        a {
        color: #5D6975;
        text-decoration: underline;
        }

        body {
        position: relative;
        width: 20cm;  
        height: 26cm; 
        margin: 0 auto; 
        color: #001028;
        background: #FFFFFF; 
        font-family: Arial, sans-serif; 
        font-size: 12px; 
        font-family: Arial;
        }

        header {
        padding: 10px 0;
        margin-top: 40px;
        margin-bottom: 30px;
        }

        #logo {
        text-align: center;
        margin-bottom: 10px;
        }

        #logo img {
        width: 90px;
        }

        h1 {
        border-top: 1px solid  #5D6975;
        border-bottom: 1px solid  #5D6975;
        color: #5D6975;
        font-size: 1.6em;
        line-height: 1.2em;
        font-weight: normal;
        text-align: center;
        margin: 0 0 20px 0;
        background: url('{{ asset('/images/dimension.png') }}');
        }

        #project {
        float: left;
        }

        #project span {
        color: #5D6975;
        text-align: right;
        width: 52px;
        margin-right: 10px;
        display: inline-block;
        font-size: 0.8em;
        }

        #company {
        float: right;
        text-align: right;
        font-size: 1.6em;
        font-weight: bold;
        }

        #project div,
        #company div {
        white-space: nowrap;        
        }

        table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 20px;
        }

        table tr:nth-child(2n-1) td {
        background: #F5F5F5 !important;
        -webkit-print-color-adjust: exact; 
        }

        table th,
        table td {
        text-align: center;
        padding: 5px;
        }

        table th {
        padding: 5px 20px;
        color: #5D6975;
        border-bottom: 1px solid #C1CED9;
        white-space: nowrap;        
        font-weight: normal;
        }

        table .service,
        table .desc {
        text-align: left;
        padding: 5px;
        }

        table td {
        padding: 20px;
        text-align: right;
        padding: 5px;
        }

        table td.service,
        table td.desc {
        vertical-align: top;
        padding: 5px;
        }

        table td.unit,
        table td.qty,
        table td.total {
        font-size: 1.2em;
        padding: 5px;
        }

        .dateheader {
        font-size: 1em;
        font-weight: bold;
        text-align: left;
        margin: 0 0 15px 0;
        }

        table td.grand {
        border-top: 1px solid #5D6975;;
        }

        #notices .notice {
        color: #5D6975;
        font-size: 1.2em;
        }

        footer {
        color: #5D6975;
        width: 100%;
        height: 30px;
        position: absolute;
        bottom: 0;
        border-top: 1px solid #C1CED9;
        padding: 8px 0;
        text-align: center;
        }
    </style>
</head>

<body>
    <header class="clearfix">
        <h1>Laporan Saldo Stok Barang</h1>
        <div id="giver" class="clearfix">
            <div></div>
        </div>
        <div class="dateheader" id="tanggal">
            {{date('d-M-y', strtotime($tanggal))}}
        </div>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th class="service">Grup</th>
                    @if($bygroup == false)
                    <th class="desc">Nama Barang</th>
                    <th class="desc">Stok</th>
                    <th class="desc">Unit</th>
                    <th class="desc">Harga per Unit</th>
                    @endif
                    <th>Nilai Stok</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6">&nbsp;</td>
                </tr>
                @php($total = 0)
                @php($index = 1)
                @if($bygroup == true) 
                    @foreach($reportData as $rd)
                        <tr>
                            <td class="service">{{$rd->groupname}}</td>
                            <td class="unit">Rp. {{number_format($rd->stock_value->getamount(), 2)}}</td>
                        </tr>
                        @php($index = $index + 1)
                        @php($total = $total+$rd->stock_value->getAmount())
                    @endforeach
                @else
                    @foreach($reportData as $rd)
                        <tr>
                            <td class="service">{{$rd->groupname}}</td>
                            <td class="desc">{{$rd->inventoryname}}</td>
                            <td class="desc">{{$rd->in_stock}}</td>
                            <td class="desc">{{$rd->unit}}</td>
                            <td class="desc">Rp. {{number_format($rd->avg_value->getamount(), 2)}}</td>
                            <td class="unit">Rp. {{number_format($rd->stock_value->getamount(), 2)}}</td>
                        </tr>
                        @php($index = $index + 1)
                        @php($total = $total+$rd->stock_value->getAmount())
                    @endforeach
                @endif
                @if($bygroup == true)
                    <tr>
                        <td colspan="1">SUBTOTAL</td>
                        <td class="total">Rp. {{number_format($total,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="1" class="grand total">GRAND TOTAL</td>
                        <td class="grand total">Rp. {{number_format($total,2)}}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="5">SUBTOTAL</td>
                        <td class="total">Rp. {{number_format($total,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="grand total">GRAND TOTAL</td>
                        <td class="grand total">Rp. {{number_format($total,2)}}</td>
                    </tr>                
                @endif
            </tbody>
        </table>
        <div id="notices">
            <div>CATATAN:</div>
        </div>
    </main>
    <footer>
        Data diambil dari server.
    </footer>
</body>

</html>
