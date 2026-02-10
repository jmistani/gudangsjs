<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pemakaian Barang</title>
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
        margin-top: 30px;
        margin-bottom: 20px;
        }

        #logo {
        text-align: center;
        margin-bottom: 10px;
        }

        #logo img {
        width: 60px;
        }

        h2 {
        border-top: 1px solid  #5D6975;
        border-bottom: 1px solid  #5D6975;
        color: #5D6975;
        font-size: 1.2em;
        line-height: 1.4em;
        font-weight: normal;
        text-align: left;
        margin: 0 0 20px 0;
        background: url('{{ asset('/images/dimension.png') }}');
        }

        #tanggal {
        float: left;
        }

        #tanggal span {
        color: #5D6975;
        text-align: right;
        width: 52px;
        margin-right: 10px;
        display: inline-block;
        font-size: 0.8em;
        }

        #giver {
        float: right;
        text-align: right;
        font-size: 1em;
        font-weight: bold;
        }

        #tanggal div,
        #giver div {
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

        table td.diterima {
        float: right;
        text-align: right;
        font-size: 1.2em;
        font-weight: normal;
        padding: 20px;
        }

        table td.penerima {
        float: right;
        text-align: right;
        font-size: 1.2em;
        font-weight: bold;
        padding: 20px;
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
        <h3>Serasi Jaya Sejati</h3>
        <h2>Pemakaian Barang ( Kode: {{ $consumeData->journalhead['code'] }})</h2>
        <div id="giver" class="clearfix">
            <div>Staff :{{ strtoupper($consumeData->giver) }}</div>
        </div>
        <div id="tanggal">
            <div><span>Tanggal: </span>{{$consumeData->post_date}}</div>
        </div>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th class="service">NO.</th>
                    <th class="desc">NAMA</th>
                    <th class="desc">MEMO</th>
                    <th>HARGA</th>
                    <th>Jumlah</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php($total = 0)
                @php($index = 1)
                @foreach($consumeData->stockMutations as $item)
                    <tr>
                        <td class="service">{{$index}}</td>
                        <td class="desc">{{$item->stockable->name}}</td>
                        <td class="desc">{{$item->description}}</td>
                        <td class="unit">Rp. {{number_format($item->price->getamount(), 2)}}</td>
                        <td class="qty">{{$item->amount *-1}}</td>
                        <td class="total">Rp. {{number_format($item->total->getAmount() * -1, 2)}}</td>
                    </tr>
                    @php($index = $index + 1)
                    @php($total = $total+$item->total->getAmount())
                @endforeach
                    <tr>
                        <td colspan="5">SUBTOTAL</td>
                        <td class="total">Rp. {{number_format($total * -1,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="grand total">GRAND TOTAL</td>
                        <td class="grand total">Rp. {{number_format($total * -1,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="Diterima">Diterima Oleh</td>
                        <td class="penerima">{{ $consumeData->receiver }}</td>
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
