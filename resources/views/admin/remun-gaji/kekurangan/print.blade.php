<html>
<head>
    <title>{{ $title }}</title>
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
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN KEKURANGAN PEMBAYARAN REMUNERASI KOMPONEN GAJI UNSUR TENAGA {{ $kategori == 1 ? 'PENDIDIK' : 'KEPENDIDIKAN' }} UNNES<br>
        BERDASAR KEPUTUSAN REKTOR NOMOR B/72/UN37/HK/2023 TANGGAL 20 JANUARI 2023 BESERTA PERUBAHANNYA<br>
        BULAN JANUARI SAMPAI MARET 2023 PADA {{ strtoupper($unit->nama) }}
    </div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" rowspan="2" width="30"><b>No</b></th>
                <th align="center" rowspan="2"><b>Nama / NIP</b></th>
                <th align="center" rowspan="2" width="30"><b>Gol</b></th>
                <th align="center" rowspan="2" width="105"><b>Jabatan</b></th>
                <th align="center" rowspan="2" width="105"><b>Sub Nama Jabatan</b></th>
                <th align="center" rowspan="2" width="30"><b>Layer</b></th>
                <th align="center" rowspan="2" width="30"><b>Grade</b></th>
                <th align="center" colspan="3"><b>Kekurangan/Kelebihan Pembayaran Remunerasi Gaji</b></th>
            </tr>
            <tr>
                <th align="center" width="60"><b>Terbayar</b></th>
                <th align="center" width="60"><b>Seharusnya</b></th>
                <th align="center" width="60"><b>Selisih / Dibayarkan</b></th>
            </tr>
            <tr>
                <th align="center"><em>1</em></th>
                <th align="center"><em>2</em></th>
                <th align="center"><em>3</em></th>
                <th align="center"><em>4</em></th>
                <th align="center"><em>5</em></th>
                <th align="center"><em>6</em></th>
                <th align="center"><em>7</em></th>
                <th align="center"><em>8</em></th>
                <th align="center"><em>9</em></th>
                <th align="center"><em>10 = 9 - 8</em></th>
            </tr>
        </thead>
        <tbody>
            @foreach($kekurangan as $key=>$k)
                <tr>
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($k->pegawai->nama) }}<br>{{ $k->pegawai->nip }}</td>
					@if($k->pegawai->golongan)
					<td align="center">{{ $k->pegawai->golongan->nama }}</td>
					@else
					<td align="center">{{ $k->golru && $k->golru->golongan ? $k->golru->golongan->nama : '-' }}</td>
					@endif
                    <td>{{ $k->jabatan ? $k->jabatan->nama : '-' }}</td>
                    <td>{{ $k->jabatan ? $k->jabatan->sub : '-' }}</td>
                    <td align="center">{{ $k->layer ? $k->layer->nama : '-' }}</td>
                    <td align="center">{{ $k->jabatan_dasar ? $k->jabatan_dasar->grade : '-' }}</td>
                    <td align="right">{{ number_format($k->dibayarkan,0,'.','.') }}</td>
                    <td align="right">{{ number_format($k->seharusnya,0,'.','.') }}</td>
                    <td align="right">{{ number_format($k->selisih,0,'.','.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="7" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($kekurangan->sum('dibayarkan'),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($kekurangan->sum('seharusnya'),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($kekurangan->sum('selisih'),0,'.','.') }}</b></td>
            </tr>
            <tr id="sign">
                <td colspan="7" width="80%"></td>
                <td colspan="3" height="80" valign="top">
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