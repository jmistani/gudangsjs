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
        font-size: 2.4em;
        line-height: 1.4em;
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
        <div id="logo">
            <img src=" {{ asset('images/logo/logovnl.png') }} ">
        </div>
        <h1>{{ $receiveData['code'] }}</h1>
        <div id="company" class="clearfix">
            <div>{{ strtoupper($receiveData->giver) }}</div>
        </div>
        <div id="project">
            <div><span>Tanggal: </span>{{$receiveData->post_date}}</div>
        </div>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th class="service">NO.</th>
                    <th class="desc">NAMA</th>
                    <th>HARGA</th>
                    <th>JUMLAH</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                        <td colspan="5">Barang Stok</td>
                    </tr>
                @php($total = 0)
                @php($index = 1)
                @foreach($receiveData->stockMutations as $item)
                    <tr>
                        <td class="service">{{$index}}</td>
                        <td class="desc">{{$item->stockable->name}}</td>
                        <td class="unit">Rp. {{number_format($item->price->getamount(), 2)}}</td>
                        <td class="qty">{{$item->amount}}</td>
                        <td class="total">Rp. {{number_format($item->total->getAmount(), 2)}}</td>
                    </tr>
                    @php($index = $index + 1)
                    @php($total = $total+$item->total->getAmount())
                @endforeach
                    <tr>
                    <tr>
                        <td colspan="5">Barang Non-Stok</td>
                    </tr>
                @php($index = 1)
                @foreach($receiveData->nonstockInventories as $item)
                    <tr>
                        <td class="service">{{$index}}</td>
                        <td class="desc">{{$item->name}}</td>
                        <td class="unit">Rp. {{number_format($item->price->getamount(), 2)}}</td>
                        <td class="qty">{{$item->amount}}</td>
                        <td class="total">Rp. {{number_format($item->subtotal->getAmount(), 2)}}</td>
                    </tr>
                    @php($index = $index + 1)
                    @php($total = $total+$item->subtotal->getAmount())
                @endforeach
                    <tr>
                        <td colspan="4">SUBTOTAL</td>
                        <td class="total">Rp. {{number_format($total,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="grand total">GRAND TOTAL</td>
                        <td class="grand total">Rp. {{number_format($total,2)}}</td>
                    </tr>
            </tbody>
        </table>
        <div id="notices">
            <div>CATATAN:</div>
            <div class="notice">Barang sudah diterima dengan baik</div>
        </div>
    </main>
    <footer>
        Data diambil dari server.
    </footer>
</body>

</html>
