<html>
<head>
    <title>DAFTAR PERHITUNGAN PEMBAYARAN KEKURANGAN GAJI PEGAWAI TETAP NON ASN UNNES</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        table {border-collapse: collapse; font-size: 12.5px;}
        table thead tr th {border: #333 solid 2px;}
        table tbody tr td {border: #333 solid 1px; padding-right: 3px; padding-left: 3px;}
        table tfoot tr td {border: #333 solid 2px;}
        table tfoot tr#sign td {border-width: 0px;}
        table tfoot #sign-content {margin-top: 20px;}
        #sign {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
        hr {margin-top: 0.75px; margin-bottom: 0.75px;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN PEMBAYARAN KEKURANGAN GAJI PEGAWAI TETAP NON ASN
        <br>
        UNIVERSITAS NEGERI SEMARANG
        <br>
        TAHUN 2023
    </div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" rowspan="2" width="30"><b>No</b></th>
                <th align="center" rowspan="2"><b>Nama / NPU</b></th>
                <th align="center" rowspan="2" width="30"><b>Gol</b></th>
                <th align="center" rowspan="2" width="50"><b>Status Kawin</b></th>
                <th align="center" colspan="3"><b>Gaji Pokok, Tunj. Suami/Istri, Tunj. Anak</b></th>
                <th align="center" rowspan="2" width="60"><b>Dibayarkan</b></th>
                <th align="center" rowspan="2" width="150"><b>Keterangan</b></th>
            </tr>
            <tr>
                <th align="center" width="60"><b>Terbayar</b></th>
                <th align="center" width="60"><b>Seharusnya</b></th>
                <th align="center" width="60"><b>Selisih</b></th>
            </tr>
            <tr>
                <th align="center"><em>1</em></th>
                <th align="center"><em>2</em></th>
                <th align="center"><em>3</em></th>
                <th align="center"><em>4</em></th>
                <th align="center"><em>5</em></th>
                <th align="center"><em>6</em></th>
                <th align="center"><em>7 = 6 - 5</em></th>
                <th align="center"><em>8</em></th>
                <th align="center"><em>9</em></th>
            </tr>
        </thead>
        <tbody>
            @foreach($j as $key=>$data)
                <tr>
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($data['pegawai']->nama) }}<br>{{ $data['pegawai']->npu != null ? $data['pegawai']->npu : $data['pegawai']->nip }}</td>
					@if($data['pegawai']->golongan)
					<td align="center">{{ $data['pegawai']->golongan->nama }}</td>
					@else
					<td align="center">-</td>
					@endif
                    <td align="center">{{ $data['status_kawin'] }}</td>
                    <td align="right">{{ number_format($data['total_terbayar'],0,'.','.') }}</td>
                    <td align="right">{{ number_format($data['total_seharusnya'],0,'.','.') }}</td>
                    <td align="right">{{ number_format($data['total_selisih'],0,'.','.') }}</td>
                    <td align="right">{{ number_format($data['total_selisih'],0,'.','.') }}</td>
                    <td>{{ $data['keterangan'] }}<br>TMT: {{ $data['tmt'] }}</td>
                </tr>

                @foreach($data['gaji'] as $gaji)
                <tr bgcolor="#e3e3e3">
                    <td></td>
                    <td colspan="2" align="center" height="20">{{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($gaji->bulan)) }} {{ $gaji->tahun }}</td>
                    <td align="center">{{ $gaji->status_kawin }}</td>
                    <td align="right">
                        {{ number_format($gaji->gjpokok,0,'.','.') }}<br>
                        {{ number_format($gaji->tjistri,0,'.','.') }}<br>
                        {{ number_format($gaji->tjanak,0,'.','.') }}
                        <hr>
                        <b>{{ number_format($gaji->gjpokok + $gaji->tjistri + $gaji->tjanak,0,'.','.') }}</b>
                    </td>
                    <td align="right">
                        {{ number_format($data['mutasi']->gaji_pokok->gaji_pokok,0,'.','.') }}<br>
                        {{ number_format($gaji->tjistri_seharusnya,0,'.','.') }}<br>
                        {{ number_format($gaji->tjanak_seharusnya,0,'.','.') }}
                        <hr>
                        <b>{{ number_format($data['mutasi']->gaji_pokok->gaji_pokok + $gaji->tjistri_seharusnya + $gaji->tjanak_seharusnya,0,'.','.') }}</b>
                    </td>
                    <td align="right">
                        {{ number_format($gaji->gjpokok_selisih,0,'.','.') }}<br>
                        {{ number_format($gaji->tjistri_selisih,0,'.','.') }}<br>
                        {{ number_format($gaji->tjanak_selisih,0,'.','.') }}
                        <hr>
                        <b>{{ number_format($gaji->gjpokok_selisih + $gaji->tjistri_selisih + $gaji->tjanak_selisih,0,'.','.') }}</b>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="4" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($grand_total['terbayar'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['seharusnya'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['selisih'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['selisih'],0,'.','.') }}</b></td>
                <td></td>
            </tr>
            <tr id="sign">
                <td colspan="7" width="80%"></td>
                <td colspan="2" height="80" valign="top">
                    <div id="sign-content">
                        Semarang,
                        <br><br>
                        Pejabat Pembuat Komitmen
                        <br><br><br><br><br>
                        Siti Mursidah, S.Pd., M.Si.
                        <br>
                        NIP. 197710262005022001
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>