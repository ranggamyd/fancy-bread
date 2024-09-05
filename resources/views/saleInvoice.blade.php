<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Fancy Cake & Bakery | Invoice {{ $sale->invoice }}</title>

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
                <h2>Invoice : {{ $sale->invoice }}</h2>
            </td>
        </tr>
    </table>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <table>
                        <tr>
                            <th colspan="3" style="text-align: left;">2VZ1.R.0607.C / RONIE JOENAIDI</th>
                        </tr>
                        <tr>
                            <th width="75px" style="text-align: left;">Nomor</th>
                            <th width="20px">:</th>
                            <td>{{ $sale->invoice }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Tanggal</th>
                            <th>:</th>
                            <td>{{ date('d/m/Y', strtotime($sale->date)) }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">PO</th>
                            <th>:</th>
                            <td>{{ $sale->code }}</td>
                        </tr>
                    </table>
                </td>
                <td class="w-half">
                    <table>
                        <tr>
                            <th width="75px" style="text-align: left;">Kepada</th>
                            <th width="20px">:</th>
                            <td>PT. Sumber Alfaria Trijaya</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <h4>{{ $sale->customer->name }}</h4>
                            </td>
                            <td></td>
                            <td>{{ $sale->customer->short_address }}</td>
                        </tr>
                        <tr>
                            <td colspan="3">{{ $sale->customer->full_address }}</td>
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
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th colspan="2">Harga</th>
                    <th colspan="2">Qty</th>
                    <th>Diskon</th>
                    <th colspan="2">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr class="items">
                    <td style="text-align: center;">{{ $item->product->code }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>Rp.</td>
                    <td style="text-align: right;">{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td>{{ $item->product->unit_type }}</td>
                    @if($item->discount)
                    <td style="text-align: right;">{{ $item->discount }} %</td>
                    @else
                    <td style="text-align: center;">-</td>
                    @endif
                    <td>Rp.</td>
                    <td style="text-align: right;">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="9" style="background-color: #fff;"></th>
                </tr>
                <tr>
                    <th colspan="3">TOTAL</th>
                    <th colspan="2">{{ $sale->total_items }}</th>
                    @if($item->discount)
                    <th>Rp.</th>
                    <th style="text-align: right;">{{ number_format($sale->total_discount, 2, ',', '.') }}</th>
                    @else
                    <th colspan="2" style="text-align: center;">-</th>
                    @endif
                    <th>Rp.</th>
                    <th style="text-align: right;">{{ number_format($sale->subtotal, 2, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="6" style="background-color: #fff;"></th>
                    <th style="text-align: left;">Harga Pengiriman</th>
                    <th>Rp.</th>
                    <th style="text-align: right;">{{ number_format($sale->shipping_price, 2, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="6" style="background-color: #fff;"></th>
                    <th style="text-align: left;">Grandtotal</th>
                    <th>Rp.</th>
                    <th style="text-align: right;">{{ number_format($sale->grandtotal, 2, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="payment-info">
        <table>
            <tr>
                <th colspan="3" style="text-align: left;">Pembayaran ditransfer ke :</th>
                <th>Penerima,</th>
                <th>Dibuat,</th>
                <th>Diperiksa,</th>
                <th>Driver,</th>
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
                <th>(..................)</th>
                <th>(..................)</th>
                <th>(..................)</th>
                <th>(..................)</th>
            </tr>
        </table>
    </div>

    <div class="footer margin-top">
        <div>Thank you</div>
        <div>&copy; Fancy Cake & Bakery</div>
    </div>
</body>

</html>