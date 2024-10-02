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
                <h2>Tanda Penyerahan TTF</h2>
            </td>
        </tr>
    </table>

    <div class="margin-top">
        <table class="w-full" style="font-size: .8rem;">
            <tr>
                <td class="w-half">
                    <table>
                        <tr>
                            <th width="100px" style="text-align: left;">Cabang</th>
                            <th width="20px">:</th>
                            <td>{{ $saleReceipt->branch }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-bottom: 16px;">Tanggal</th>
                            <th style="padding-bottom: 16px;">:</th>
                            <td style="padding-bottom: 16px;">{{ date('d/m/Y', strtotime($saleReceipt->date)) }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Nama Supplier</th>
                            <th>:</th>
                            <td>Fancy Cake & Bakery</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Kode Supplier</th>
                            <th>:</th>
                            <td>VZ01.R.0607.C / RONIE</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Alamat Email</th>
                            <th>:</th>
                            <td>Fancybakerycirebon@gmail.com</td>
                        </tr>
                    </table>
                </td>
                <td class="w-half">
                    <table>
                        <tr>
                            <th colspan="3" style="text-align: left;">Penyerahan Faktur (PF)</th>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: center; padding-bottom: 16px;">{{ $saleReceipt->code }}</td>
                        </tr>
                        <tr>
                            <th width="100px" style="text-align: left;">No. Telepon</th>
                            <th width="20px">:</th>
                            <td>089620053867</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">No. Faximile</th>
                            <th width="20px">:</th>
                            <td>0231-201973</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Contact Person</th>
                            <th width="20px">:</th>
                            <td>Rika</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="margin-top">
        <table class="products">
            <thead>
                <tr>
                    <th rowspan="2" style="border: .5px solid #fff;">No</th>
                    <th rowspan="2" style="border: .5px solid #fff;">No. LPB</th>
                    <th colspan="3" style="border: .5px solid #fff;">Faktur</th>
                    <th rowspan="2" style="border: .5px solid #fff;">Faktur Pajak</th>
                    <th rowspan="2" colspan="2" style="border: .5px solid #fff;">Total</th>
                </tr>
                <tr>
                    <th style="border: .5px solid #fff;">No.</th>
                    <th colspan="2" style="border: .5px solid #fff;">Rp.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleReceipt->saleReceiptInvoices as $item)
                <tr class="items">
                    <td style="text-align: center;">{{ $loop->index + 1 }}</td>
                    <td style="text-align: center;">{{ $item->sale->goods_receipt_number }}</td>
                    <td style="text-align: center;">{{ $item->sale->invoice }}</td>
                    <td>Rp.</td>
                    <td style="text-align: right;">{{ number_format($item->sale->grandtotal, 2, ',', '.') }}</td>
                    <td></td>
                    <td>Rp.</td>
                    <td style="text-align: right;">{{ number_format($item->sale->grandtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="9" style="background-color: #fff;"></th></tr>
                <tr>
                    <th colspan="6">TOTAL</th>
                    <th>Rp.</th>
                    <th style="text-align: right;">{{ number_format($saleReceipt->subtotal, 2, ',', '.') }}</th>
                </tr>
                <tr><th colspan="9" style="background-color: #fff;"></th></tr>
                <tr><th colspan="8">Kami setuju dengan ketentuan penyerahan faktur yang berlaku di PT. Sumber Alfaria Trijaya</th></tr>
                <tr><th colspan="8"></th></tr>
                <tr><th colspan="8"></th></tr>
                <tr><th colspan="8"></th></tr>
                <tr><th colspan="8"></th></tr>
                <tr><th colspan="8"></th></tr>
                <tr><th colspan="8">(..................)</th></tr>
            </tfoot>
        </table>
    </div>

    <div class="footer margin-top">
        <div>Thank you</div>
        <div>&copy; Fancy Cake & Bakery</div>
    </div>
</body>

</html>