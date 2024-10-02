<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Fancy Cake & Bakery | Receipt {{ $saleReceipt->code }}</title>

    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }

        h4 {
            margin: 0;
        }

        .w-full {
            width: 100%;
        }

        .w-half {
            width: 50%;
        }

        .margin-top {
            margin-top: 1.25rem;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table.products {
            /* font-size: 0.875rem; */
        }

        table.products tr {
            background-color: rgb(245 158 11);
        }

        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }

        table tr.items {
            background-color: rgb(241 245 249);
        }

        table tr.items td {
            padding: 0.5rem;
        }

        table.products tfoot tr {
            background-color: rgb(241 245 249);
        }

        table.products tfoot th {
            color: #555;
            padding: 0.5rem;
        }

        .payment-info {
            /* font-size: 0.875rem; */
            padding: 1rem;
            /* background-color: rgb(241 245 249); */
        }

        .footer {
            /* font-size: 0.875rem; */
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
    </style>
</head>

<body>
    <table class="w-full">
        <tr>
            <td class="w-half">
                <!-- <img src="{{ asset('laraveldaily.png') }}" alt="laravel daily" width="200" /> -->
                <h2 style="margin: 0;">Fancy Cake & Bakery</h2>
                <small>Jl. Widarasari III No. 3, Cirebon</small>
            </td>
            <td class="w-half">
                <h2>KWITANSI : FC/KWT/{{ $saleReceipt->id }}</h2>
            </td>
        </tr>
    </table>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <th style="text-align: left;">Nomor</th>
                <th width="20px">:</th>
                <td>FC/KWT/{{ $saleReceipt->id }}</td>
            </tr>
            <tr>
                <th style="text-align: left;">Telah terima dari</th>
                <th>:</th>
                <td>PT. Sumber Alfaria Trijaya</td>
            </tr>
            <tr>
                <th style="text-align: left;">Uang Sejumlah</th>
                <th>:</th>
                <td>Rp. {{ number_format($saleReceipt->total_invoice, 0, ',', '.') }},-</td>
            </tr>
            <tr>
                <th style="text-align: left;">Untuk Pembayaran</th>
                <th>:</th>
                <td>Invoice dengan rincian PF No : {{ $saleReceipt->code }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: .6rem;">Terbilang</th>
                <th>:</th>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center; padding: .6rem; background-color: rgb(241 245 249);">
                    @php Config::set('terbilang.locale', 'id') @endphp
                    {{ Str::title(Terbilang::make($saleReceipt->total_invoice, ' rupiah')) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="payment-info">
        <table>
            <tr>
                <th colspan="3" style="text-align: left;">Pembayaran ditransfer ke :</th>
                <td style="text-align: center;">Cirebon, {{ date('d M Y', strtotime($saleReceipt->date)) }}</td style="text-align: center;">
            </tr>
            <tr>
                <th width="75px" style="text-align: left;">Bank</th>
                <th width="20px">:</th>
                <td>UOB</td>
            </tr>
            <tr>
                <th width="75px" style="text-align: left;">No. Ac</th>
                <th width="20px">:</th>
                <td>310 300 131 3</td>
            </tr>
            <tr>
                <th width="75px" style="text-align: left;">A/N</th>
                <th width="20px">:</th>
                <td>Inge S Hong Hwa Ing</td>
                <th>(............................)</th>
            </tr>
        </table>
    </div>

    <div class="footer margin-top">
        <div>Thank you</div>
        <div>&copy; Fancy Cake & Bakery</div>
    </div>
</body>

</html>