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
        height: 25cm; 
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

        reportheader {
        border-top: 1px solid  #5D6975;
        border-bottom: 1px solid  #5D6975;
        color: #5D6975;
        font-size: 2.0em;
        line-height: 1.4em;
        font-weight: normal;
        text-align: left;
        margin: 0 0 20px 0;
        background: url('{{ asset('/images/dimension.png') }}');
        }

        .subheader {
        border-top: 1px solid;
        border-bottom: 1px solid;
        font-size: 1.2em;
        font-weight: 300;
        text-align: left;
        margin: 0 0 15px 0;
        }

        .dateheader {
        font-size: 1em;
        font-weight: 500;
        text-align: left;
        margin: 0 0 15px 0;
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
            padding: 8px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
        }
    </style>
</head>

<body>
    <header class="clearfix">
        <h2 class="reportheader">Laporan Akun: {{$account}}</h2>
        <div id="giver" class="clearfix">
            <div></div>
        </div>
        <div id="tanggal">
            {{$tanggal_mulai}} s/d {{$tanggal_akhir}}
        </div>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th class="desc">Tanggal</th>
                    <th class="desc">Keterangan</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php($prevdate = null)
                @php($currdate = null)
                @php($saldo = 0)
                @php($totalcredit = 0)
                @php($totaldebit = 0)
                @php($index = 1)
                @foreach($transactions as $tr)
                    <tr>
                        @php($saldo = $saldo + $tr->debit->getamount() - $tr->credit->getamount())
                        <td class="desc">{{date('d-M-y h:m', strtotime($tr->post_date))}}</td>
                        <td class="desc">{{$tr->memo}}</td>
                        <td class="unit">Rp. {{number_format($tr->debit->getamount(), 2)}}</td>
                        <td class="unit">Rp. {{number_format($tr->credit->getamount(), 2)}}</td>
                        <td class="unit">Rp. {{number_format($saldo, 2)}}</td>
                    </tr>
                    @php($index = $index + 1)
                    @php($totaldebit = $totaldebit+$tr->debit->getAmount())
                    @php($totalcredit = $totalcredit+$tr->credit->getAmount())
                @endforeach
                <tr>
                    <td colspan="4">Saldo</td>
                    <td class="total">Rp. {{number_format($saldo,2)}}</td>
                </tr>
            </tbody>
        </table>
    </main>
    <footer>
    </footer>
</body>

</html>
